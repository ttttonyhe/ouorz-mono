import { z } from "zod"

import {
	apiHandler,
	ApiError,
	fetchJSON,
	PUBLIC_CACHE,
	requireEnv,
} from "~/lib/apiHandler"

const MAX_PAGES = 100
const ETH_WALLET = "0x2650f08Da54F7019f9a3306bad0dfc8474644eAD"
const SOL_WALLET = "G9T9yXeWLyspA9xLSLwZYvAPbSNV9E2NU7jWLpQDW6Re"

const tokenIdSchema = z
	.string()
	.regex(/^(?:0x[\da-fA-F]{1,64}|\d{1,78})$/u)
	.transform(BigInt)
	.refine((value) => value < 2n ** 256n)
	.transform((value) => value.toString())

const ethNFTSchema = z.object({
	contract: z.object({ address: z.string().regex(/^0x[\da-fA-F]{40}$/u) }),
	id: z.object({
		tokenId: tokenIdSchema,
		tokenMetadata: z
			.object({ tokenType: z.string().default("") })
			.default({ tokenType: "" }),
	}),
	balance: z.string(),
	title: z
		.string()
		.nullish()
		.transform((value) => value ?? ""),
	description: z
		.string()
		.nullish()
		.transform((value) => value ?? ""),
	media: z.array(z.object({ raw: z.string() })).default([]),
})
const ethPageSchema = z.object({
	ownedNfts: z.array(ethNFTSchema).max(100),
	pageKey: z.string().min(1).max(2048).optional(),
})

const solNFTSchema = z.object({
	name: z
		.string()
		.nullish()
		.transform((value) => value ?? ""),
	description: z
		.string()
		.nullish()
		.transform((value) => value ?? ""),
	imageUrl: z
		.string()
		.nullish()
		.transform((value) => value ?? ""),
	tokenAddress: z.string().regex(/^[1-9A-HJ-NP-Za-km-z]{32,44}$/u),
})
const solPageSchema = z.object({
	result: z.object({
		assets: z.array(solNFTSchema).max(40),
		totalPages: z.number().int().min(0).max(MAX_PAGES),
		pageNumber: z.number().int().positive(),
	}),
})

export type EthNFT = z.infer<typeof ethNFTSchema>
export type SolNFT = z.infer<typeof solNFTSchema>

const getEthereumNFTs = async (
	url: URL,
	signal: AbortSignal
): Promise<EthNFT[]> => {
	const assets = new Map<string, EthNFT>()
	const cursors = new Set<string>()

	for (let page = 1; page <= MAX_PAGES; page++) {
		// oxlint-disable-next-line no-await-in-loop -- Each page supplies the cursor for the next request.
		const data = await fetchJSON(ethPageSchema, url, { signal })
		for (const item of data.ownedNfts) {
			if (item.media[0]?.raw) {
				assets.set(
					`${item.contract.address.toLowerCase()}/${item.id.tokenId}`,
					item
				)
			}
		}
		if (!data.pageKey) return [...assets.values()]
		if (cursors.has(data.pageKey))
			throw new ApiError(502, "Invalid NFT pagination")
		cursors.add(data.pageKey)
		url.searchParams.set("pageKey", data.pageKey)
	}
	throw new ApiError(502, "NFT pagination limit exceeded")
}

const getSolanaNFTs = async (
	url: string,
	signal: AbortSignal
): Promise<SolNFT[]> => {
	const assets = new Map<string, SolNFT>()

	for (let page = 1; page <= MAX_PAGES; page++) {
		// oxlint-disable-next-line no-await-in-loop -- Validate each provider page before requesting the next.
		const { result } = await fetchJSON(solPageSchema, url, {
			method: "POST",
			headers: { "Content-Type": "application/json" },
			signal,
			body: JSON.stringify({
				id: page,
				jsonrpc: "2.0",
				method: "qn_fetchNFTs",
				params: {
					wallet: SOL_WALLET,
					omitFields: ["provenance", "traits"],
					page,
					perPage: 40,
				},
			}),
		})
		if (result.pageNumber !== page)
			throw new ApiError(502, "Invalid NFT pagination")
		for (const item of result.assets) {
			if (item.imageUrl) assets.set(item.tokenAddress, item)
		}
		if (page >= result.totalPages) return [...assets.values()]
	}
	throw new ApiError(502, "NFT pagination limit exceeded")
}

export default apiHandler(["GET", "HEAD"], async (_req, res) => {
	const ethURL = new URL(
		`${requireEnv("ALCHEMY_API_PATH").replace(/\/$/u, "")}/getNFTs`
	)
	ethURL.search = new URLSearchParams({
		owner: ETH_WALLET,
		withMetadata: "true",
		pageSize: "100",
		tokenUriTimeoutInMs: "5000",
	}).toString()
	const solURL = requireEnv("QUICK_NODE_API_PATH")
	const controller = new AbortController()
	const signal = AbortSignal.any([
		controller.signal,
		AbortSignal.timeout(10_000),
	])

	try {
		const [eth, sol] = await Promise.all([
			getEthereumNFTs(ethURL, signal),
			getSolanaNFTs(solURL, signal),
		])
		res.setHeader("Cache-Control", PUBLIC_CACHE)
		return res.status(200).json({ eth, sol })
	} finally {
		controller.abort()
	}
})
