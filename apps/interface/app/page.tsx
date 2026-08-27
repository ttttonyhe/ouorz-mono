"use client"

import Artifact from "@ouorz/twilight-protocol/artifacts-zk/contracts/TwilightBlog.sol/TwilightBlog.json"
import Address from "@ouorz/twilight-protocol/deployment/address.json"
import { useEffect, useState } from "react"
import { BrowserProvider, Contract, Provider, type Signer } from "zksync-ethers"

const ERA_TESTNET_RPC = "https://testnet.era.zksync.dev"

interface EthereumProvider {
	selectedAddress: string | null
	request: (payload: { method: string }) => Promise<string[]>
	on: (event: "accountsChanged", handler: (accounts: string[]) => void) => void
	removeListener: (
		event: "accountsChanged",
		handler: (accounts: string[]) => void
	) => void
}

interface Category {
	id: string
	name: string
	description: string
}

interface RawCategory {
	id: bigint
	name: string
	description: string
}

// SAFETY: `ethereum` is injected by the wallet extension and is absent otherwise,
// so it is read as an optional property and every caller handles `undefined`.
const getEthereum = (): EthereumProvider | undefined =>
	(globalThis as { ethereum?: EthereumProvider }).ethereum

class BlogContractInterface {
	private constructor(
		readonly provider: Provider,
		readonly signer: Signer,
		readonly contract: Contract
	) {}

	static async connect(
		ethereum: EthereumProvider,
		selectedAddress: string
	): Promise<BlogContractInterface> {
		const provider = new Provider(ERA_TESTNET_RPC)
		const signer = await new BrowserProvider(ethereum).getSigner(
			selectedAddress
		)
		const contract = new Contract(
			Address.TwilightBlogProxy,
			Artifact.abi,
			signer
		)
		return new BlogContractInterface(provider, signer, contract)
	}

	getAuthor(): Promise<string> {
		return this.contract.author()
	}

	getBlogUri(): Promise<string> {
		return this.contract.blogUri()
	}

	async getCategories(): Promise<Category[]> {
		const categories: RawCategory[] = await this.contract.categories()
		return categories.map((category) => ({
			id: category.id.toString(),
			name: category.name,
			description: category.description,
		}))
	}

	async updateBlogUri(blogUri: string): Promise<void> {
		await this.contract.updateBlogUri(blogUri)
	}

	async createCategory(
		id: number,
		name: string,
		description: string
	): Promise<void> {
		await this.contract.createCategory(id, name, description)
	}
}

const Page = () => {
	const [contractInterface, setContractInterface] =
		useState<BlogContractInterface>()
	const [account, setAccount] = useState("")
	const [blogUri, setBlogUri] = useState("")
	const [authorAddress, setAuthorAddress] = useState("")
	const [categories, setCategories] = useState<Category[]>([])

	useEffect(() => {
		const ethereum = getEthereum()
		if (!ethereum) return

		/**
		 * The wallet is usually unauthorised on first load, so the contract interface is
		 * rebuilt from the account itself rather than only at mount. `generation` drops a
		 * connection whose account was superseded while it was still resolving.
		 */
		let generation = 0
		let cancelled = false

		const attach = (address: string | null) => {
			generation += 1
			const attempt = generation

			setAccount(address ?? "")
			setContractInterface(undefined)

			if (!address) return

			BlogContractInterface.connect(ethereum, address).then((instance) => {
				if (cancelled || attempt !== generation) return instance
				setContractInterface(instance)
				return instance
			}, console.error)
		}

		const onAccountsChanged = (accounts: string[]) =>
			attach(accounts[0] ?? null)

		ethereum.on("accountsChanged", onAccountsChanged)
		attach(ethereum.selectedAddress)

		return () => {
			cancelled = true
			ethereum.removeListener("accountsChanged", onAccountsChanged)
		}
	}, [])

	const requestConnection = () => {
		getEthereum()
			?.request({ method: "eth_requestAccounts" })
			.catch(console.error)
	}

	return (
		<main>
			<h1>Hello {account}</h1>
			<ul>
				<li>Blog uri: {blogUri}</li>
				<li>Author address: {authorAddress}</li>
				{categories.map((category) => (
					<li key={category.id}>
						{category.name} : {category.description}
					</li>
				))}
			</ul>
			<button type="button" onClick={requestConnection}>
				Connect
			</button>
			<button
				type="button"
				onClick={() => {
					void contractInterface?.getBlogUri().then(setBlogUri, console.error)
				}}>
				Get blog uri
			</button>
			<button
				type="button"
				onClick={() => {
					void contractInterface
						?.getAuthor()
						.then(setAuthorAddress, console.error)
				}}>
				Get author address
			</button>
			<button
				type="button"
				onClick={() => {
					void contractInterface
						?.getCategories()
						.then(setCategories, console.error)
				}}>
				Get categories
			</button>
			<button
				type="button"
				onClick={() => {
					void contractInterface?.updateBlogUri("newUri").catch(console.error)
				}}>
				Update blog uri
			</button>
			<button
				type="button"
				onClick={() => {
					void contractInterface
						?.createCategory(2, "Cate2", "Cate2 description")
						.catch(console.error)
				}}>
				Create new category
			</button>
		</main>
	)
}

export default Page
