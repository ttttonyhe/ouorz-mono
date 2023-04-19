import { useTheme } from "next-themes"
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
			className="group flex items-center dark:bg-gray-700 dark:border-gray-700 border rounded-md shadow-sm hover:shadow-md transition-shadow bg-white cursor-pointer w-50 z-40"
		>
			<div className="lg:group-hover:opacity-0 opacity-100 overflow-hidden rounded-[5px] h-full">
				<div className="w-full lg:h-[196px] h-auto bg-gray-200 dark:bg-gray-800 border-b dark:border-gray-700">
					<img
						className="rounded-tl-md rounded-tr-md w-full h-full z-10"
						src={imageURL}
						alt={title}
					/>
				</div>
				<div className="px-3.5 pt-4.5 pb-5">
					<h2 className="text-normal font-medium tracking-wider mb-0.5 whitespace-nowrap text-ellipsis overflow-hidden">
						{title}
					</h2>
					<p
						className="text-sm tracking-wide text-gray-600 dark:text-gray-400 line-clamp-2 leading-snug"
						dangerouslySetInnerHTML={{
							__html: description,
						}}
					/>
				</div>
			</div>
			<div className="lg:block hidden absolute group-hover:opacity-100 opacity-0 overflow-hidden rounded-md transition-opacity ease-in-out duration-300 h-full left-5 top-8 w-40">
				<div className="translate-y-1 group-hover:translate-y-0 transition-all duration-300 ease-in-out">
					<img
						className="rounded-md w-10 h-10 mb-2 border"
						src={imageURL}
						alt={title}
					/>
					<h2 className="text-sm font-bold tracking-wider mb-1 text-black dark:text-white">
						{title}
					</h2>
					<p
						className="text-xs font-medium tracking-wide text-gray-800 dark:text-gray-300 line-clamp-2 group-hover:line-clamp-none leading-snug"
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
	const { resolvedTheme } = useTheme()
	return (
		<div className="flex items-center dark:bg-gray-800 dark:border dark:border-gray-700 rounded-md border shadow-sm hover:shadow-md transition-shadow bg-white w-50 z-40">
			<ContentLoader
				className={resolvedTheme === undefined ? "opacity-50" : ""}
				uniqueKey={props.uniqueKey}
				speed={2}
				width={100}
				style={{ width: "100%" }}
				height={305}
				backgroundColor={resolvedTheme === "dark" ? "#525252" : "#f3f3f3"}
				foregroundColor={resolvedTheme === "dark" ? "#373737" : "#ecebeb"}
			>
				<rect x="0" y="0" rx="5" ry="5" width="99.7%" height="195" />
				<rect x="15" y="222" rx="5" ry="5" width="50%" height="20" />
				<rect x="15" y="247" rx="5" ry="5" width="80%" height="15" />
				<rect x="15" y="267" rx="5" ry="5" width="70%" height="15" />
			</ContentLoader>
		</div>
	)
}

export { PodcastCard, PodcastCardLoading }
