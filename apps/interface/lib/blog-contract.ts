import abi from "@ouorz/twilight-protocol/dist/TwilightBlog.json"
import {
	BrowserProvider,
	Contract,
	getAddress,
	isError,
	MaxUint256,
	ZeroAddress,
	type ContractTransactionResponse,
} from "ethers"
import { z } from "zod"

import {
	readWalletAccounts,
	readWalletChain,
	walletAddress,
	type EthereumProvider,
} from "./wallet"

const categoryResult = z
	.tuple([z.bigint().nonnegative().max(MaxUint256), z.string(), z.string()])
	.transform(([id, name, description]) => ({
		id: id.toString(),
		name,
		description,
	}))
const categoryPage = z.array(categoryResult)

export type Category = z.output<typeof categoryResult>

export class BlogContract {
	private closed = false

	private constructor(
		private readonly ethereum: EthereumProvider,
		private readonly provider: BrowserProvider,
		private readonly contract: Contract,
		readonly account: string,
		private readonly chainId: bigint
	) {}

	static async connect(
		ethereum: EthereumProvider
	): Promise<BlogContract | undefined> {
		const accounts = await readWalletAccounts(ethereum)
		if (accounts.length === 0) return undefined
		const account = accounts[0]
		const address = process.env.NEXT_PUBLIC_BLOG_ADDRESS
		const configuredChain = process.env.NEXT_PUBLIC_BLOG_CHAIN_ID
		if (!address || !configuredChain || !/^[1-9]\d*$/u.test(configuredChain)) {
			throw new Error(
				"Set NEXT_PUBLIC_BLOG_ADDRESS and NEXT_PUBLIC_BLOG_CHAIN_ID for this deployment."
			)
		}
		const chainId = BigInt(configuredChain)
		const contractAddress = getAddress(address)
		if (contractAddress === ZeroAddress)
			throw new Error("The blog address cannot be zero.")

		const provider = new BrowserProvider(ethereum, undefined, {
			cacheTimeout: -1,
		})
		try {
			if ((await provider.getNetwork()).chainId !== chainId) {
				throw new Error(
					`Switch your wallet to chain ${chainId} and connect again.`
				)
			}
			if ((await provider.getCode(contractAddress)) === "0x") {
				throw new Error(
					"No blog contract is deployed at the configured address on this chain."
				)
			}
			const signer = await provider.getSigner(account)
			return new BlogContract(
				ethereum,
				provider,
				new Contract(contractAddress, abi, signer),
				account,
				chainId
			)
		} catch (error) {
			provider.destroy()
			throw error
		}
	}

	close(): void {
		this.closed = true
		this.provider.destroy()
	}

	async getAuthor(): Promise<string> {
		return walletAddress.parse(
			await this.contract.getFunction("author").staticCall()
		)
	}

	async getBlogUri(): Promise<string> {
		return z
			.string()
			.parse(await this.contract.getFunction("blogUri").staticCall())
	}

	async getCategories(): Promise<Category[]> {
		const categories: Category[] = []
		const blockTag = await this.provider.getBlockNumber()
		for (let start = 0; ; start += 256) {
			this.assertOpen()
			// Each page determines whether another request is needed.
			// oxlint-disable-next-line no-await-in-loop
			const result: unknown = await this.contract
				.getFunction("categories")
				.staticCall(start, start + 256, { blockTag })
			const page = categoryPage.parse(result)
			categories.push(...page)
			if (page.length < 256) return categories
		}
	}

	async updateBlogUri(uri: string): Promise<void> {
		if (!uri.trim()) throw new Error("The blog URI cannot be empty.")
		await this.requireAuthor()
		await this.confirm(
			await this.contract
				.getFunction("updateBlogUri")
				.send(uri, { chainId: this.chainId })
		)
	}

	async createDemoCategory(): Promise<void> {
		const categories = await this.getCategories()
		const ids = new Set(categories.map((category) => BigInt(category.id)))
		let id = BigInt(2)
		while (ids.has(id)) id += BigInt(1)
		if (id > MaxUint256) throw new Error("No category identifier is available.")
		await this.requireAuthor()
		await this.confirm(
			await this.contract
				.getFunction("createCategory")
				.send(id, `Cate${id}`, `Cate${id} description`, {
					chainId: this.chainId,
				})
		)
	}

	private assertOpen(): void {
		if (this.closed)
			throw new Error("The wallet connection changed. Connect again.")
	}

	private async requireAuthor(): Promise<void> {
		this.assertOpen()
		const [chain, accounts, author] = await Promise.all([
			readWalletChain(this.ethereum),
			readWalletAccounts(this.ethereum),
			this.getAuthor(),
		])
		this.assertOpen()
		if (chain !== this.chainId || accounts[0] !== this.account) {
			throw new Error("The wallet connection changed. Connect again.")
		}
		if (getAddress(author) !== this.account)
			throw new Error("Only the blog author can make this change.")
	}

	private async confirm(
		transaction: ContractTransactionResponse
	): Promise<void> {
		try {
			const receipt = await transaction.wait(1, 120_000)
			if (!receipt || receipt.status !== 1)
				throw new Error("The transaction was not confirmed.")
		} catch (error) {
			if (
				isError(error, "TRANSACTION_REPLACED") &&
				!error.cancelled &&
				error.receipt.status === 1
			)
				return
			if (isError(error, "TIMEOUT")) {
				throw new Error(
					`Transaction ${transaction.hash} is still pending. Check your wallet before retrying.`,
					{ cause: error }
				)
			}
			throw error
		}
	}
}
