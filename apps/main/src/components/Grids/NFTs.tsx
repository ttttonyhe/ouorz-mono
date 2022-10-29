import React from 'react'
import useSWR from 'swr'
import fetcher from '~/lib/fetcher'
import { ResDataType } from '~/pages/api/nft'
import { NFTCard } from '~/components/Card/NFT'
import CardEmpty from '~/components/Card/Empty'
import { NFTCardLoading } from '~/components/Card/NFT'

const NFTs = () => {
	const { data, error } = useSWR<ResDataType>('api/nft', fetcher)

	if (error) {
		return <CardEmpty />
	}

	if (!data) {
		return (
			<>
				<NFTCardLoading uniqueKey="nft-card-skeleton-1" />
				<NFTCardLoading uniqueKey="nft-card-skeleton-2" />
				<NFTCardLoading uniqueKey="nft-card-skeleton-3" />
			</>
		)
	}

	if (
		data &&
		(!data.eth || !data.eth.length || !data.sol || !data.eth.length)
	) {
		return <CardEmpty />
	}

	return (
		<>
			{data.eth.map((item, index: React.Key) => {
				return (
					<NFTCard
						key={index}
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
			{data.sol.map((item, index: React.Key) => {
				return (
					<NFTCard
						key={index}
						image={item.imageUrl}
						title={item.name}
						description={item.description}
						blockchain="solana"
						token={item.tokenAddress}
					/>
				)
			})}
		</>
	)
}

export default NFTs
