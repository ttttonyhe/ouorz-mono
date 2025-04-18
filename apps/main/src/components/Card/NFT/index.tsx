import { Icon } from "@twilight-toolkit/ui"
import ContentLoader from "react-content-loader"
import { GlowingBackground } from "~/components/Visual"
import openLink from "~/utilities/externalLink"

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
	let icon = "question"
	let tokenLink = props.link
	let link = props.link
	let imageSrc = props.image
	const description = props.description || "Just another NFT"

	// Determine token link based on blockchain
	switch (props.blockchain) {
		case "ethereum":
			icon = "eth"
			tokenLink = `https://etherscan.io/address/${props.contract}`
			break
		case "solana":
			icon = "solana"
			tokenLink = `https://solscan.io/token/${props.token}`
			link = tokenLink
			break
	}

	// Map ipfs hash to gateway url
	if (imageSrc.includes("ipfs://")) {
		imageSrc = `https://dweb.link/ipfs/${imageSrc.replace("ipfs://", "")}`
	}

	return (
		<div className="glowing-div w-50 z-40 flex cursor-pointer items-center rounded-md border bg-white shadow-xs transition-shadow hover:shadow-md dark:border-none dark:bg-gray-700">
			<GlowingBackground />
			<div className="glowing-div-content bottom-0 left-0 right-0 top-[0.5px] h-full overflow-hidden rounded-[5px]">
				<div
					onClick={() => openLink(link)}
					className="h-[196px] w-full border-b bg-gray-200 dark:border-gray-700 dark:bg-gray-800 lg:w-[196px]">
					<div className="absolute top-3 z-20 flex w-full justify-between px-3 text-gray-600 dark:text-gray-100">
						<label className="rounded-full bg-white p-[1.5px] shadow-xs dark:bg-gray-700 dark:bg-opacity-50">
							<span className="flex h-[1rem] w-[1rem]">
								<Icon name={icon} />
							</span>
						</label>
						{props.tokenType && (
							<label className="z-20 flex items-center rounded-full bg-white px-2 py-0.5 text-xs shadow-xs dark:bg-gray-700 dark:bg-opacity-50">
								{props.tokenType}
							</label>
						)}
					</div>
					<img
						className="z-10 h-full w-full rounded-tl-md rounded-tr-md"
						src={imageSrc}
						alt={props.title}
					/>
				</div>
				<div className="px-3.5 pb-5">
					<div className="z-30 -mt-3.5 mb-3 flex w-full justify-center">
						<a
							className="inline-block w-28 overflow-hidden text-ellipsis rounded-full border border-gray-300 bg-gray-100 px-2 py-0.5 text-sm text-gray-600 shadow-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
							href={tokenLink}
							target="_blank"
							rel="noreferrer">
							{props.contract || props.token}
						</a>
					</div>
					<div onClick={() => openLink(link)}>
						<h2 className="mb-0.5 overflow-hidden text-ellipsis whitespace-nowrap text-2 font-medium tracking-wider">
							{props.title}
						</h2>
						<p className="line-clamp-2 text-3 leading-snug tracking-wide text-gray-600 dark:text-gray-400">
							{description}
						</p>
					</div>
				</div>
			</div>
		</div>
	)
}

const NFTCardLoading = (props: { uniqueKey: string }) => {
	return (
		<div className="glowing-div w-50 z-40 flex items-center rounded-md border bg-white p-[1px] shadow-xs transition-shadow hover:shadow-md dark:border dark:border-gray-700 dark:bg-gray-800">
			<ContentLoader
				className="block dark:hidden"
				uniqueKey={`${props.uniqueKey}-light`}
				speed={2}
				width={100}
				style={{ width: "100%" }}
				height={305}
				backgroundColor="#f3f3f3"
				foregroundColor="#ecebeb">
				<rect x="0" y="0" rx="5" ry="5" width="100%" height="190" />
				<rect x="15" y="210" rx="5" ry="5" width="50%" height="25" />
				<rect x="15" y="245" rx="5" ry="5" width="80%" height="15" />
				<rect x="15" y="270" rx="5" ry="5" width="70%" height="15" />
			</ContentLoader>
			<ContentLoader
				className="hidden dark:block"
				uniqueKey={`${props.uniqueKey}-dark`}
				speed={2}
				width={100}
				style={{ width: "100%" }}
				height={305}
				backgroundColor="#525252"
				foregroundColor="#737373">
				<rect x="0" y="0" rx="5" ry="5" width="100%" height="190" />
				<rect x="15" y="210" rx="5" ry="5" width="50%" height="25" />
				<rect x="15" y="245" rx="5" ry="5" width="80%" height="15" />
				<rect x="15" y="270" rx="5" ry="5" width="70%" height="15" />
			</ContentLoader>
		</div>
	)
}

export { NFTCard, NFTCardLoading }
