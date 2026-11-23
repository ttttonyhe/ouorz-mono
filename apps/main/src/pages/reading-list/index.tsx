import { Icon, type IconProps } from "@twilight-toolkit/ui"
import type { GetServerSideProps } from "next"
import Head from "next/head"
import Link from "next/link"
import { useRouter } from "next/router"
import { Fragment } from "react"
import { BookCard } from "~/components/Card/Book"
import { pageLayout } from "~/components/Page"
import { getGoodreadsBooks, type Book } from "~/content/goodreads"
import type { NextPageWithLayout } from "~/pages/_app"
import {
	getViewTransitionName,
	navigateWithTransition,
} from "~/utilities/viewTransition"

interface ReadingListProps {
	mustRead: Book[]
	currentlyReading: Book[]
	read: Book[]
	toRead: Book[]
}

interface ShelfSection {
	id: string
	label: string
	icon: IconProps["name"]
	iconClassName: string
	books: Book[]
}

const ReadingList: NextPageWithLayout<ReadingListProps> = ({
	mustRead,
	currentlyReading,
	read,
	toRead,
}: ReadingListProps) => {
	const router = useRouter()
	const visibleShelves: ShelfSection[] = [
		{
			id: "curated",
			label: "Curated",
			icon: "bookShelf" as const,
			iconClassName: "mr-1.5 flex h-[22px] w-[22px] text-purple-500",
			books: mustRead,
		},
		{
			id: "currently-reading",
			label: "Currently Reading",
			icon: "eye" as const,
			iconClassName: "mr-1.5 flex h-[22px] w-[22px] text-green-500",
			books: currentlyReading,
		},
		{
			id: "read",
			label: "Read",
			icon: "checkDouble" as const,
			iconClassName: "mr-1.5 flex h-[22px] w-[22px] text-yellow-500",
			books: read,
		},
		{
			id: "to-read",
			label: "To Read",
			icon: "bookmark" as const,
			iconClassName: "mr-1.5 flex h-5 w-5 text-blue-500",
			books: toRead,
		},
	].filter((shelf) => shelf.books.length > 0)

	return (
		<div>
			<Head>
				<link
					rel="icon"
					href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📚</text></svg>"
				/>
			</Head>
			<section className="mt-0 pt-24 lg:mt-20 lg:pt-0">
				<div className="mb-4 flex items-center">
					<div className="flex flex-1 items-center">
						<div className="mt-1 mr-4.5 flex -rotate-6 cursor-pointer items-center">
							<span className="text-[35px] drop-shadow-lg hover:animate-spin">
								📚
							</span>
						</div>
						<div>
							<h1 className="flex items-center gap-x-1.5 text-[28px] font-medium tracking-wide whitespace-nowrap text-black dark:text-white">
								<span
									style={{
										viewTransitionName: getViewTransitionName("Reading List"),
									}}>
									Reading List
								</span>
							</h1>
							<p className="-mt-1 text-sm text-neutral-500 dark:text-gray-400">
								Some books are to be tasted; others swallowed; and some few to
								be chewed and digested. <i>—— Francis Bacon</i>
							</p>
						</div>
					</div>
					<div className="mt-2 flex h-full items-center justify-end whitespace-nowrap">
						<div className="flex-1 pr-2 pl-5">
							<p className="text-xl text-gray-500 dark:text-gray-400">
								<Link
									href="/pages"
									onNavigate={(event) => {
										event.preventDefault()
										navigateWithTransition(router, "/pages")
									}}
									className="flex cursor-pointer items-center">
									<span className="mr-2 h-6 w-6">
										<Icon name="left" />
									</span>
									Pages
								</Link>
							</p>
						</div>
					</div>
				</div>
			</section>
			<div className="my-5">
				<hr className="dark:border-gray-600" />
			</div>
			{visibleShelves.length === 0 ? (
				<section className="mb-28 rounded-md border border-gray-200 bg-white px-6 py-8 text-center dark:border-gray-700 dark:bg-gray-800">
					<p className="text-gray-500 dark:text-gray-400">
						Reading list is temporarily unavailable. Please try again shortly.
					</p>
				</section>
			) : (
				visibleShelves.map((shelf, index) => {
					const isLast = index === visibleShelves.length - 1
					return (
						<Fragment key={shelf.id}>
							<section className={isLast ? "mb-28" : "mb-10"}>
								<h2 className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 pt-[4px] pb-1 font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
									<span className={shelf.iconClassName}>
										<Icon name={shelf.icon} />
									</span>
									<span className="uppercase">{shelf.label}</span>
								</h2>
								<div className="mt-4">
									<div className="grid grid-cols-2 gap-4">
										{shelf.books.map((book) => (
											<BookCard key={book.link} {...book} />
										))}
									</div>
								</div>
							</section>
							{!isLast && (
								<div className="mb-10">
									<hr className="dark:border-gray-600" />
								</div>
							)}
						</Fragment>
					)
				})
			)}
		</div>
	)
}

ReadingList.layout = pageLayout

const unwrapBooks = (result: PromiseSettledResult<Book[]>) =>
	result.status === "fulfilled" ? result.value : []

export const getServerSideProps: GetServerSideProps<ReadingListProps> = async ({
	res,
}) => {
	const [mustReadResult, currentlyReadingResult, readResult, toReadResult] =
		await Promise.allSettled([
			getGoodreadsBooks("mustRead"),
			getGoodreadsBooks("currentlyReading"),
			getGoodreadsBooks("read"),
			getGoodreadsBooks("toRead"),
		])
	const mustRead = unwrapBooks(mustReadResult)
	const currentlyReading = unwrapBooks(currentlyReadingResult)
	const read = unwrapBooks(readResult)
	const toRead = unwrapBooks(toReadResult)
	const allSucceeded = [
		mustReadResult,
		currentlyReadingResult,
		readResult,
		toReadResult,
	].every((result) => result.status === "fulfilled")
	const totalBooks =
		mustRead.length + currentlyReading.length + read.length + toRead.length

	if (totalBooks === 0) {
		// Return 503 instead of an empty 200 page so crawlers do not treat outages as soft 404s.
		res.statusCode = 503
		res.setHeader("Retry-After", "300")
		res.setHeader("Cache-Control", "no-store")
	} else {
		res.setHeader(
			"Cache-Control",
			allSucceeded
				? "public, s-maxage=86400, stale-while-revalidate=604800"
				: "public, s-maxage=300, stale-while-revalidate=3600"
		)
	}

	return {
		props: { mustRead, currentlyReading, read, toRead },
	}
}

export default ReadingList
