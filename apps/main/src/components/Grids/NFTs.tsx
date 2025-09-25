import useSWR from "swr"
import CardEmpty from "~/components/Card/Empty"
import { NFTCard, NFTCardLoading } from "~/components/Card/NFT"
import fetcher from "~/lib/fetcher"
import type { EthNFT, SolNFT } from "~/pages/api/nft"

type NFTApiResData = {
	eth: EthNFT[]
	sol: SolNFT[]
}

const NFTs = () => {
	const { data, error } = useSWR<NFTApiResData>("api/nft", fetcher)

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

	if (
		data &&
		(!data.eth || !data.eth.length || !data.sol || !data.eth.length)
	) {
		return <CardEmpty />
	}

	return (
		<div className="grid grid-cols-2 gap-4 lg:grid-cols-3">
			{data.eth.map((item) => {
				return (
					<NFTCard
						key={item.contract.address}
						image={item.media[0].raw}
						title={item.title}
						description={item.description}
						tokenType={item.id.tokenMetadata.tokenType}
						blockchain="ethereum"
						contract={item.contract.address}
						link={item.tokenUri.raw}
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
