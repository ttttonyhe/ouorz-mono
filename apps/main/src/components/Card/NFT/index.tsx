import { Icon } from "@twilight-toolkit/ui"
import { GlowingBackground } from "~/components/Visual"
import openLink from "~/utilities/externalLink"

interface NFTContent {
	title: string
	description: string
	image: string
	tokenType?: string
	quantity?: number
}

type PropsType = NFTContent &
	(
		| {
				blockchain: "ethereum"
				tokenId: string
				contract: string
				token?: never
		  }
		| { blockchain: "solana"; token: string; contract?: never; tokenId?: never }
	)

const NFTCard = (props: PropsType) => {
	const icon = props.blockchain === "ethereum" ? "eth" : "solana"
	const link =
		props.blockchain === "ethereum"
			? `https://etherscan.io/nft/${props.contract}/${props.tokenId}`
			: `https://solscan.io/token/${props.token}`
	const tokenLink =
		props.blockchain === "ethereum"
			? `https://etherscan.io/address/${props.contract}`
			: link
	let imageSrc = props.image
	const description = props.description || "Just another NFT"

	if (imageSrc.startsWith("ipfs://")) {
		imageSrc = imageSrc.replace(
			/^ipfs:\/\/(?:ipfs\/)?/u,
			"https://dweb.link/ipfs/"
		)
	}

	return (
		<div className="glowing-div z-40 flex w-50 cursor-pointer items-center rounded-md border bg-white shadow-xs transition-shadow hover:shadow-md dark:border-none dark:bg-gray-700">
			<GlowingBackground />
			<div className="glowing-div-content top-[0.5px] right-0 bottom-0 left-0 h-full overflow-hidden rounded-[5px]">
				<div
					onClick={() => openLink(link)}
					className="h-[196px] w-full border-b bg-gray-200 lg:w-[196px] dark:border-gray-700 dark:bg-gray-800">
					<div className="absolute top-3 z-20 flex w-full justify-between px-3 text-gray-600 dark:text-gray-100">
						<span className="rounded-full bg-white p-[1.5px] shadow-xs dark:bg-gray-700/50">
							<span className="flex h-[1rem] w-[1rem]">
								<Icon name={icon} />
							</span>
						</span>
						{props.tokenType && (
							<span className="z-20 flex items-center rounded-full bg-white px-2 py-0.5 text-xs shadow-xs dark:bg-gray-700/50">
								{props.tokenType}
							</span>
						)}
					</div>
					<img
						className="z-10 h-full w-full rounded-tl-md rounded-tr-md"
						src={imageSrc}
						alt={props.title}
						loading="lazy"
					/>
				</div>
				<div className="px-3.5 pb-5">
					<div className="z-30 -mt-3.5 mb-3 flex w-full justify-center">
						<a
							className="inline-block w-28 overflow-hidden rounded-full border border-gray-300 bg-gray-100 px-2 py-0.5 text-sm text-ellipsis text-gray-600 shadow-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
							href={tokenLink}
							target="_blank"
							rel="noopener noreferrer">
							{props.contract || props.token}
						</a>
					</div>
					<div onClick={() => openLink(link)}>
						<h3 className="mb-0.5 overflow-hidden text-2 font-medium tracking-wider text-ellipsis whitespace-nowrap">
							{props.title}
						</h3>
						<p className="line-clamp-2 text-3 leading-snug tracking-wide text-gray-600 dark:text-gray-400">
							{description}
						</p>
					</div>
				</div>
			</div>
		</div>
	)
}

const NFTCardLoading = () => (
	<div
		aria-hidden="true"
		className="glowing-div z-40 flex h-[305px] w-50 flex-col rounded-md border bg-white p-px shadow-xs dark:border-gray-700 dark:bg-gray-800">
		<div className="h-[190px] w-full animate-pulse rounded-md bg-gray-100 dark:bg-neutral-600" />
		<div className="flex flex-col gap-y-2.5 p-3.5">
			<div className="h-[25px] w-1/2 animate-pulse rounded-md bg-gray-100 dark:bg-neutral-600" />
			<div className="h-[15px] w-4/5 animate-pulse rounded-md bg-gray-100 dark:bg-neutral-600" />
			<div className="h-[15px] w-7/10 animate-pulse rounded-md bg-gray-100 dark:bg-neutral-600" />
		</div>
	</div>
)

export { NFTCard, NFTCardLoading }
