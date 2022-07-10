import type { NextApiRequest, NextApiResponse } from 'next'
import { withSentry } from '@sentry/nextjs'

type ETH_NFT = {
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
type SOL_NFT = {
	name: string
	description: string
	imageUrl: string
	tokenAddress: string
}
export type ResDataType = {
	eth: ETH_NFT[]
	sol: SOL_NFT[]
}

const handler = async (
	_req: NextApiRequest,
	res: NextApiResponse<ResDataType>
) => {
	// Fetch ETH NFTs
	const eth_response = await fetch(
		`${process.env.ALCHEMY_API_PATH}/getNFTs?owner=0x8FE6fE9EC2a34D9e77Cdfeb5B2eaab5DfD8C2542`,
		{
			method: 'GET',
			headers: { 'content-type': 'application/json' },
		}
	)
	const eth_data = await eth_response.json()
	// Only return NFTs with media content
	eth_data['ownedNfts'] = eth_data['ownedNfts'].filter(
		(nft: ETH_NFT) => nft.media[0].raw !== ''
	)

	// Fetch SOL NFTs
	const sol_response = await fetch(process.env.QUICK_NODE_API_PATH, {
		method: 'POST',
		headers: { 'content-type': 'application/json' },
		body: JSON.stringify({
			id: 67,
			jsonrpc: '2.0',
			method: 'qn_fetchNFTs',
			params: {
				wallet: 'HCc9D96ZLZvkRpnQyETVGijTKMeJy5xWpzt2KS75dHXS',
				omitFields: ['provenance', 'traits'],
				page: 1,
				perPage: 40,
			},
		}),
	})
	const sol_data = await sol_response.json()

	res.setHeader(
		'Cache-Control',
		'public, s-maxage=1200, stale-while-revalidate=600'
	)
	return res.status(200).json({
		eth: eth_data['ownedNfts'],
		sol: sol_data['result']['assets'],
	})
}

export default withSentry(handler)
