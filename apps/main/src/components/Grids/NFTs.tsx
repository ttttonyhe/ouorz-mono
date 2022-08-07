import React from 'react'
import useSWR from 'swr'
import fetcher from '~/lib/fetcher'
import { ResDataType } from '~/pages/api/nft'
import { NFTCard } from '~/components/Card/NFT'

const NFTs = () => {
	const { data } = useSWR<ResDataType>('/api/nft', fetcher, { suspense: true })

	return (
		<div className="grid lg:grid-cols-3 grid-cols-2 gap-4">
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
		</div>
	)
}

export default NFTs
