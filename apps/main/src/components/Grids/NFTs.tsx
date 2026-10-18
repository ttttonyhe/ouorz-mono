import useSWR from "swr"
import { z } from "zod"

import CardEmpty from "~/components/Card/Empty"
import { NFTCard, NFTCardLoading } from "~/components/Card/NFT"
import createFetcher from "~/lib/fetcher"

const nftResponseSchema = z.object({
	eth: z.array(
		z.object({
			contract: z.object({ address: z.string().regex(/^0x[\da-fA-F]{40}$/u) }),
			id: z.object({
				tokenId: z.string().regex(/^\d+$/u),
				tokenMetadata: z.object({ tokenType: z.string() }),
			}),
			title: z.string(),
			description: z.string(),
			media: z
				.tuple([z.object({ raw: z.string().min(1) })])
				.rest(z.object({ raw: z.string() })),
		})
	),
	sol: z.array(
		z.object({
			tokenAddress: z.string().regex(/^[1-9A-HJ-NP-Za-km-z]{32,44}$/u),
			imageUrl: z.string().min(1),
			name: z.string(),
			description: z.string(),
		})
	),
})

const fetchNFTs = createFetcher(nftResponseSchema)

const NFTs = () => {
	const { data, error } = useSWR<z.infer<typeof nftResponseSchema>, Error>(
		"/api/nft",
		fetchNFTs
	)

	if (error) {
		return <CardEmpty />
	}

	if (!data) {
		return (
			<div className="grid grid-cols-2 gap-4 lg:grid-cols-3">
				<NFTCardLoading uniqueKey="nft-card-skeleton-1" />
				<NFTCardLoading uniqueKey="nft-card-skeleton-2" />
				<NFTCardLoading uniqueKey="nft-card-skeleton-3" />
			</div>
		)
	}

	if (data.eth.length === 0 && data.sol.length === 0) {
		return <CardEmpty />
	}

	return (
		<div className="grid grid-cols-2 gap-4 lg:grid-cols-3">
			{data.eth.map((item) => {
				return (
					<NFTCard
						key={`${item.contract.address}-${item.id.tokenId}`}
						image={item.media[0].raw}
						title={item.title}
						description={item.description}
						tokenType={item.id.tokenMetadata.tokenType}
						blockchain="ethereum"
						contract={item.contract.address}
						tokenId={item.id.tokenId}
					/>
				)
			})}
			{data.sol.map((item) => {
				return (
					<NFTCard
						key={item.tokenAddress}
						image={item.imageUrl}
						title={item.name}
						description={item.description}
						blockchain="solana"
						token={item.tokenAddress}
					/>
				)
			})}
		</div>
	)
}

export default NFTs
