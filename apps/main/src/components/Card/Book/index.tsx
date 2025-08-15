import Image from "next/image"
import ContentLoader from "react-content-loader"
import blurDataURL from "~/constants/blurDataURL"
import { Book } from "~/pages/api/goodreads"
import openLink from "~/utilities/externalLink"

const BookCard = (props: Book) => {
	const { title, author, imageURL, link, dateAdded } = props

	return (
		<div
			onClick={() => openLink(link)}
			className="shadow-xs group z-40 flex w-full cursor-pointer flex-col rounded-md border bg-white transition-all duration-300 hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600 dark:hover:shadow-none">
			<div className="flex flex-1 items-center lg:justify-center">
				<div className="shrink-0 px-4.5 py-4">
					<Image
						width={35}
						height={52}
						src={imageURL}
						alt={title}
						className="rounded-xs shadow-xs border shadow-gray-200 dark:shadow-none"
						placeholder="blur"
						blurDataURL={blurDataURL}
						loading="lazy"
					/>
				</div>
				<div className="overflow-hidden py-2 pr-4.5 lg:px-0 lg:group-hover:hidden lg:group-hover:w-0 lg:group-hover:delay-75">
					<p className="lg:text-normal overflow-hidden text-ellipsis whitespace-nowrap text-sm font-medium leading-tight tracking-wider dark:text-white">
						{title}
					</p>
					<p className="mt-1 overflow-hidden text-ellipsis whitespace-nowrap text-xs font-light tracking-wide text-gray-500 dark:text-gray-400 lg:text-sm">
						by {author}
					</p>
				</div>
				<div className="line-clamp-3! hidden pr-4.5 opacity-0 transition-opacity duration-200 ease-in-out lg:block lg:grow lg:group-hover:flex-1 lg:group-hover:opacity-100 lg:group-hover:delay-75">
					<p className="hidden text-xs font-medium dark:text-white lg:group-hover:block">
						{title}
					</p>
				</div>
			</div>
			<div className="flex w-full items-center justify-between border-t border-gray-100 px-4.5 py-2 text-xs font-light text-gray-500 dark:border-gray-700 dark:text-gray-400">
				<span>Date Added</span>
				<span>{dateAdded}</span>
			</div>
		</div>
	)
}

const BookCardLoading = (props: { uniqueKey: string }) => {
	return (
		<div className="shadow-xs z-40 flex w-full items-center rounded-md border bg-white p-px dark:border dark:border-gray-700 dark:bg-gray-800">
			<ContentLoader
				className="block dark:hidden"
				uniqueKey={`${props.uniqueKey}-light`}
				speed={2}
				width={100}
				style={{ width: "100%" }}
				height={107}
				backgroundColor="#f3f3f3"
				foregroundColor="#ecebeb">
				<rect x="15" y="13" rx="5" ry="5" width="35" height="52" />
				<rect x="65" y="18" rx="5" ry="5" width="65%" height="20" />
				<rect x="65" y="43" rx="5" ry="5" width="50%" height="15" />
				<rect x="0" y="79" rx="0" ry="0" width="100%" height="1" />
				<rect x="15" y="87" rx="5" ry="5" width="68" height="15" />
				<rect x="76%" y="87" rx="5" ry="5" width="19%" height="15" />
			</ContentLoader>
			<ContentLoader
				className="hidden dark:block"
				uniqueKey={`${props.uniqueKey}-dark`}
				speed={2}
				width={100}
				style={{ width: "100%" }}
				height={107}
				backgroundColor="#525252"
				foregroundColor="#737373">
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
