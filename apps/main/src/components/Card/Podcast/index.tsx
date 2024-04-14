import ContentLoader from "react-content-loader"
import openLink from "~/utilities/externalLink"

interface PodcastCardProps {
	title: string
	description: string
	imageURL: string
	link: string
}

const PodcastCard = (props: PodcastCardProps) => {
	const { title, description, imageURL, link } = props

	return (
		<div
			onClick={() => openLink(link)}
			className="w-50 group z-40 flex cursor-pointer items-center rounded-md border bg-white shadow-sm transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-700">
			<div className="h-full overflow-hidden rounded-[5px] opacity-100 lg:group-hover:opacity-0">
				<div className="h-auto w-full border-b bg-gray-200 dark:border-gray-700 dark:bg-gray-800 lg:h-[196px]">
					<img
						className="z-10 h-full w-full rounded-tl-md rounded-tr-md"
						src={imageURL}
						alt={title}
					/>
				</div>
				<div className="px-3.5 pb-5 pt-4.5">
					<h2 className="text-normal mb-0.5 overflow-hidden text-ellipsis whitespace-nowrap font-medium tracking-wider">
						{title}
					</h2>
					<p
						className="line-clamp-2 text-sm leading-snug tracking-wide text-gray-600 dark:text-gray-400"
						dangerouslySetInnerHTML={{
							__html: description,
						}}
					/>
				</div>
			</div>
			<div className="absolute left-5 top-8 hidden h-full w-40 overflow-hidden rounded-md opacity-0 transition-opacity duration-300 ease-in-out group-hover:opacity-100 lg:block">
				<div className="translate-y-1 transition-all duration-300 ease-in-out group-hover:translate-y-0">
					<img
						className="mb-2 h-10 w-10 rounded-md border"
						src={imageURL}
						alt={title}
					/>
					<h2 className="mb-1 text-sm font-bold tracking-wider text-black dark:text-white">
						{title}
					</h2>
					<p
						className="line-clamp-2 text-xs font-medium leading-snug tracking-wide text-gray-800 group-hover:line-clamp-none dark:text-gray-300"
						dangerouslySetInnerHTML={{
							__html: description,
						}}
					/>
				</div>
			</div>
		</div>
	)
}

const PodcastCardLoading = (props: { uniqueKey: string }) => {
	return (
		<div className="w-50 z-40 flex items-center rounded-md border bg-white shadow-sm transition-shadow hover:shadow-md dark:border dark:border-gray-700 dark:bg-gray-800">
			<ContentLoader
				className="block dark:hidden"
				uniqueKey={`${props.uniqueKey}-light`}
				speed={2}
				width={100}
				style={{ width: "100%" }}
				height={305}
				backgroundColor="#f3f3f3"
				foregroundColor="#ecebeb">
				<rect x="0" y="0" rx="5" ry="5" width="99.7%" height="195" />
				<rect x="15" y="222" rx="5" ry="5" width="50%" height="20" />
				<rect x="15" y="247" rx="5" ry="5" width="80%" height="15" />
				<rect x="15" y="267" rx="5" ry="5" width="70%" height="15" />
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
				<rect x="0" y="0" rx="5" ry="5" width="99.7%" height="195" />
				<rect x="15" y="222" rx="5" ry="5" width="50%" height="20" />
				<rect x="15" y="247" rx="5" ry="5" width="80%" height="15" />
				<rect x="15" y="267" rx="5" ry="5" width="70%" height="15" />
			</ContentLoader>
		</div>
	)
}

export { PodcastCard, PodcastCardLoading }
