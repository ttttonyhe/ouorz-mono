import type { NextRequest } from "next/server"

type EthNFT = {
	contract: {
		address: string
	}
	id: {
		tokenMetadata: {
			tokenType: string
		}
	}
	balance: string
	title: string
	description: string
	media: { raw: string }[]
	tokenUri: {
		raw: string
	}
}

type SolNFT = {
	name: string
	description: string
	imageUrl: string
	tokenAddress: string
}

const ETH_WALLET = "0x2650f08Da54F7019f9a3306bad0dfc8474644eAD"
const SOL_WALLET = "G9T9yXeWLyspA9xLSLwZYvAPbSNV9E2NU7jWLpQDW6Re"

const nft = async (_req: NextRequest) => {
	// Fetch ETH NFTs
	const ethData: {
		ownedNfts: EthNFT[]
	} = await fetch(
		`${process.env.ALCHEMY_API_PATH}/getNFTs?owner=${ETH_WALLET}`,
		{
			method: "GET",
			headers: { "content-type": "application/json" },
		}
	)
		.then((res) => {
			if (res.status !== 200) return []
			return res.json()
		})
		.catch((err) => {
			console.error(err)
			return []
		})

	// Only return NFTs with media content
	ethData["ownedNfts"] = ethData["ownedNfts"]
		? ethData["ownedNfts"].filter((nft) => nft.media[0].raw !== "")
		: []

	// Fetch SOL NFTs
	const solData: {
		result: {
			assets?: SolNFT[]
		}
	} = await fetch(process.env.QUICK_NODE_API_PATH, {
		method: "POST",
		headers: { "content-type": "application/json" },
		body: JSON.stringify({
			id: 67,
			jsonrpc: "2.0",
			method: "qn_fetchNFTs",
			params: {
				wallet: SOL_WALLET,
				omitFields: ["provenance", "traits"],
				page: 1,
				perPage: 40,
			},
		}),
	})
		.then((res) => {
			if (res.status !== 200) return []
			return res.json()
		})
		.catch((err) => {
			console.error(err)
			return []
		})

	if (!solData["result"]) {
		solData["result"] = {
			assets: [],
		}
	}

	return new Response(
		JSON.stringify({
			eth: ethData["ownedNfts"],
			sol: solData["result"]["assets"],
		}),
		{
			status: 200,
			headers: {
				"content-type": "application/json",
				"cache-control": "public, s-maxage=1200, stale-while-revalidate=600",
			},
		}
	)
}

export const config = {
	runtime: "edge",
}

export default nft
