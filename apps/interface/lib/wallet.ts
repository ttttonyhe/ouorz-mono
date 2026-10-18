import { getAddress, isAddress, type Eip1193Provider } from "ethers"
import { z } from "zod"

export interface EthereumProvider extends Eip1193Provider {
	on(
		event: "accountsChanged" | "chainChanged" | "disconnect",
		handler: () => void
	): void
	removeListener(
		event: "accountsChanged" | "chainChanged" | "disconnect",
		handler: () => void
	): void
}

declare global {
	interface Window {
		ethereum?: EthereumProvider
	}
}

export const getEthereum = (): EthereumProvider | undefined => window.ethereum

export const walletAddress = z.string().refine(isAddress).transform(getAddress)
export const walletAccounts = z.array(walletAddress)
export const walletChain = z
	.string()
	.regex(/^0x[\da-f]+$/iu)
	.transform(BigInt)

export const readWalletAccounts = async (
	ethereum: EthereumProvider
): Promise<string[]> =>
	walletAccounts.parse(await ethereum.request({ method: "eth_accounts" }))

export const readWalletChain = async (
	ethereum: EthereumProvider
): Promise<bigint> =>
	walletChain.parse(await ethereum.request({ method: "eth_chainId" }))

// Zod.catch supplies a parse default; it is not a promise handler.
// oxlint-disable-next-line unicorn/prefer-top-level-await
export const walletError = z
	.object({
		code: z.union([z.string(), z.number()]).optional(),
		message: z.string(),
		reason: z.string().nullable().optional(),
	})
	.catch({ message: "The wallet request failed." })
	.transform((error) => {
		if (error.code === "ACTION_REJECTED" || error.code === 4001) {
			return new Error("The wallet request was cancelled.", { cause: error })
		}
		return new Error(error.reason ?? error.message, { cause: error })
	})
