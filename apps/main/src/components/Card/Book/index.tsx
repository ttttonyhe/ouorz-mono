import Image from "next/image"
import { useTheme } from "next-themes"
import ContentLoader from "react-content-loader"
import openLink from "~/utilities/externalLink"
import { Book } from "~/pages/api/goodreads"
import blurDataURL from "~/constants/blurDataURL"

const BookCard = (props: Book) => {
	const { title, author, imageURL, link, dateAdded } = props

	return (
		<div
			onClick={() => openLink(link)}
			className="group flex flex-col dark:bg-gray-800 dark:border-gray-700 border rounded-md shadow-sm dark:hover:shadow-none hover:shadow-gray-200 hover:-translate-y-0.5 hover:border-gray-300 dark:hover:border-gray-600 transition-all duration-300 bg-white cursor-pointer w-50 z-40"
		>
			<div className="flex flex-1 items-center lg:justify-center">
				<div className="px-4.5 py-4 flex-shrink-0">
					<Image
						width={35}
						height={52}
						src={imageURL}
						alt={title}
						className="rounded-sm shadow-sm shadow-gray-200 dark:shadow-none border"
						placeholder="blur"
						blurDataURL={blurDataURL}
						loading="lazy"
					/>
				</div>
				<div className="lg:group-hover:hidden lg:group-hover:delay-75 lg:group-hover:w-0 py-2 lg:px-0 pr-4.5 overflow-hidden">
					<p className="dark:text-white text-sm lg:text-normal font-medium tracking-wider leading-tight whitespace-nowrap overflow-hidden overflow-ellipsis">
						{title}
					</p>
					<p className="text-gray-500 dark:text-gray-400 lg:text-sm text-xs font-light tracking-wide mt-1 whitespace-nowrap text-ellipsis overflow-hidden">
						by {author}
					</p>
				</div>
				<div className="pr-4.5 hidden lg:flex-grow lg:block lg:group-hover:opacity-100 lg:group-hover:delay-75 opacity-0 lg:group-hover:flex-1 transition-opacity ease-in-out duration-200 !line-clamp-3">
					<p className="lg:group-hover:block hidden text-xs font-medium dark:text-white">
						{title}
					</p>
				</div>
			</div>
			<div className="flex justify-between items-center w-full px-4.5 py-2 text-xs text-gray-500 dark:text-gray-400 font-light border-t border-gray-100 dark:border-gray-700">
				<span>Date Added</span>
				<span>{dateAdded}</span>
			</div>
		</div>
	)
}

const BookCardLoading = (props: { uniqueKey: string }) => {
	return (
		<div className="flex items-center dark:bg-gray-800 dark:border dark:border-gray-700 rounded-md border shadow-sm bg-white w-50 z-40 p-[1px]">
			<ContentLoader
				className="dark:hidden block"
				uniqueKey={`${props.uniqueKey}-light`}
				speed={2}
				width={100}
				style={{ width: "100%" }}
				height={107}
				backgroundColor="#f3f3f3"
				foregroundColor="#ecebeb"
			>
				<rect x="15" y="13" rx="5" ry="5" width="35" height="52" />
				<rect x="65" y="18" rx="5" ry="5" width="65%" height="20" />
				<rect x="65" y="43" rx="5" ry="5" width="50%" height="15" />
				<rect x="0" y="79" rx="0" ry="0" width="100%" height="1" />
				<rect x="15" y="87" rx="5" ry="5" width="68" height="15" />
				<rect x="76%" y="87" rx="5" ry="5" width="19%" height="15" />
			</ContentLoader>
			<ContentLoader
				className="dark:block hidden"
				uniqueKey={`${props.uniqueKey}-dark`}
				speed={2}
				width={100}
				style={{ width: "100%" }}
				height={107}
				backgroundColor="#525252"
				foregroundColor="#737373"
			>
				<rect x="15" y="13" rx="5" ry="5" width="35" height="52" />
				<rect x="65" y="18" rx="5" ry="5" width="65%" height="20" />
				<rect x="65" y="43" rx="5" ry="5" width="50%" height="15" />
				<rect x="0" y="79" rx="0" ry="0" width="100%" height="1" />
				<rect x="15" y="87" rx="5" ry="5" width="68" height="15" />
				<rect x="76%" y="87" rx="5" ry="5" width="19%" height="15" />
			</ContentLoader>
		</div>
	)
}

export { BookCard, BookCardLoading }
