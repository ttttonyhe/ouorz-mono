import Image from "next/image"

import blurDataURL from "~/constants/blurDataURL"
import type { Book } from "~/content/goodreads"

const BookCard = (props: Book) => {
	const { title, author, imageURL, link, dateAdded } = props

	return (
		<a
			href={link}
			aria-label={`${title} by ${author}`}
			target="_blank"
			rel="noreferrer"
			className="group z-40 flex w-full cursor-pointer flex-col rounded-md border bg-white shadow-xs transition-all duration-300 hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600 dark:hover:shadow-none">
			<div className="flex flex-1 items-center lg:justify-center">
				<div className="shrink-0 px-4.5 py-4">
					<Image
						width={35}
						height={52}
						src={imageURL}
						alt={title}
						className="rounded-xs border shadow-xs shadow-gray-200 dark:shadow-none"
						placeholder="blur"
						blurDataURL={blurDataURL}
						loading="lazy"
					/>
				</div>
				<div className="overflow-hidden py-2 pr-4.5 lg:px-0 lg:group-hover:hidden lg:group-hover:w-0 lg:group-hover:delay-75">
					<h3 className="lg:text-normal overflow-hidden text-sm leading-tight font-medium tracking-wider text-ellipsis whitespace-nowrap dark:text-white">
						{title}
					</h3>
					<p className="mt-1 overflow-hidden text-xs font-light tracking-wide text-ellipsis whitespace-nowrap text-gray-500 lg:text-sm dark:text-gray-400">
						by {author}
					</p>
				</div>
				<div
					aria-hidden="true"
					className="line-clamp-3! hidden pr-4.5 opacity-0 transition-opacity duration-200 ease-in-out lg:block lg:grow lg:group-hover:flex-1 lg:group-hover:opacity-100 lg:group-hover:delay-75">
					<p className="hidden text-xs font-medium lg:group-hover:block dark:text-white">
						{title}
					</p>
				</div>
			</div>
			<div className="flex w-full items-center justify-between border-t border-gray-100 px-4.5 py-2 text-xs font-light text-gray-500 dark:border-gray-700 dark:text-gray-400">
				<span>Date Added</span>
				<time dateTime={dateAdded.replaceAll("/", "-")}>{dateAdded}</time>
			</div>
		</a>
	)
}

export { BookCard }
