import { useTheme } from 'next-themes'
import ContentLoader from 'react-content-loader'
import { GlowingBackground } from '~/components/Visual'
import { Icon } from '@twilight-toolkit/ui'
import openLink from '~/utilities/externalLink'

interface PropsType {
	blockchain: string
	title: string
	description: string
	image: string
	link?: string
	contract?: string
	token?: string
	tokenType?: string
	quantity?: number
}

const NFTCard = (props: PropsType) => {
	let icon = 'question'
	let tokenLink = props.link
	let link = props.link
	let imageSrc = props.image
	const description = props.description || 'Just another NFT'

	// Determine token link based on blockchain
	switch (props.blockchain) {
		case 'ethereum':
			icon = 'eth'
			tokenLink = `https://etherscan.io/address/${props.contract}`
			break
		case 'solana':
			icon = 'solana'
			tokenLink = `https://solscan.io/token/${props.token}`
			link = tokenLink
			break
	}

	// Map ipfs hash to gateway url
	if (imageSrc.includes('ipfs://')) {
		imageSrc = `https://dweb.link/ipfs/${imageSrc.replace('ipfs://', '')}`
	}

	return (
		<div className="glowing-div flex items-center dark:bg-gray-700 dark:border-none border rounded-md shadow-sm hover:shadow-md transition-shadow bg-white cursor-pointer w-50 z-40">
			<GlowingBackground />
			<div className="glowing-div-content top-[0.5px] right-0 bottom-0 left-0 overflow-hidden rounded-[5px] h-full">
				<div
					onClick={() => openLink(link)}
					className="lg:w-[196px] w-full h-[196px] bg-gray-200 dark:bg-gray-800 border-b dark:border-gray-700"
				>
					<div className="absolute top-3 px-3 z-20 flex justify-between text-gray-600 dark:text-gray-100 w-full">
						<label className="p-[1.5px] shadow-sm bg-white dark:bg-gray-700 dark:bg-opacity-50 rounded-full">
							<span className="w-[1rem] h-[1rem] flex">
								<Icon name={icon} />
							</span>
						</label>
						{props.tokenType && (
							<label className="py-0.5 px-2 shadow-sm text-xs flex items-center bg-white dark:bg-gray-700 dark:bg-opacity-50 rounded-full z-20">
								{props.tokenType}
							</label>
						)}
					</div>
					<img
						className="rounded-tl-md rounded-tr-md w-full h-full z-10"
						src={imageSrc}
						alt={props.title}
					/>
				</div>
				<div className="px-3.5 pb-5">
					<div className="w-full -mt-3.5 mb-3 justify-center flex z-30">
						<a
							className="overflow-hidden inline-block text-ellipsis shadow-sm w-28 text-sm text-gray-600 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-600 rounded-full border px-2 bg-gray-100 border-gray-300 py-0.5"
							href={tokenLink}
							target="_blank"
							rel="noreferrer"
						>
							{props.contract || props.token}
						</a>
					</div>
					<div onClick={() => openLink(link)}>
						<h2 className="text-2 font-medium tracking-wider mb-0.5 whitespace-nowrap text-ellipsis overflow-hidden">
							{props.title}
						</h2>
						<p className="text-3 tracking-wide text-gray-600 dark:text-gray-400 line-clamp-2 leading-snug">
							{description}
						</p>
					</div>
				</div>
			</div>
		</div>
	)
}

const NFTCardLoading = (props: { uniqueKey: string }) => {
	const { resolvedTheme } = useTheme()
	return (
		<div className="glowing-div flex items-center dark:bg-gray-800 dark:border dark:border-gray-700 rounded-md border shadow-sm hover:shadow-md transition-shadow bg-white w-50 z-40 p-[1px]">
			<ContentLoader
				className={resolvedTheme === undefined ? 'opacity-50' : ''}
				uniqueKey={props.uniqueKey}
				speed={2}
				width={100}
				style={{ width: '100%' }}
				height={305}
				backgroundColor={resolvedTheme === 'dark' ? '#525252' : '#f3f3f3'}
				foregroundColor={resolvedTheme === 'dark' ? '#373737' : '#ecebeb'}
			>
				<rect x="0" y="0" rx="5" ry="5" width="100%" height="190" />
				<rect x="15" y="210" rx="5" ry="5" width="50%" height="25" />
				<rect x="15" y="245" rx="5" ry="5" width="80%" height="15" />
				<rect x="15" y="270" rx="5" ry="5" width="70%" height="15" />
			</ContentLoader>
		</div>
	)
}

export { NFTCard, NFTCardLoading }
