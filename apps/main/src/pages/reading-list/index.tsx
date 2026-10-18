import { Icon } from "@twilight-toolkit/ui"
import type { GetServerSideProps } from "next"
import Head from "next/head"
import { useRouter } from "next/router"

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

const ReadingList: NextPageWithLayout<ReadingListProps> = ({
	mustRead,
	currentlyReading,
	read,
	toRead,
}: ReadingListProps) => {
	const router = useRouter()

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
								<button
									type="button"
									onClick={() => navigateWithTransition(router, "/pages")}
									className="flex cursor-pointer items-center">
									<span className="mr-2 h-6 w-6">
										<Icon name="left" />
									</span>
									Pages
								</button>
							</p>
						</div>
					</div>
				</div>
			</section>
			<div className="my-5">
				<hr className="dark:border-gray-600" />
			</div>
			<section className="mb-10">
				<h2 className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 pt-[4px] pb-1 font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-[22px] w-[22px] text-purple-500">
						<Icon name="bookShelf" />
					</span>
					<span className="uppercase">Curated</span>
				</h2>
				<div className="mt-4">
					<div className="grid grid-cols-2 gap-4">
						{mustRead.map((book) => (
							<BookCard key={book.link} {...book} />
						))}
					</div>
				</div>
			</section>
			<div className="mb-10">
				<hr className="dark:border-gray-600" />
			</div>
			<section className="mb-10">
				<h2 className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 pt-[4px] pb-1 font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-[22px] w-[22px] text-green-500">
						<Icon name="eye" />
					</span>
					<span className="uppercase">Currently Reading</span>
				</h2>
				<div className="mt-4">
					<div className="grid grid-cols-2 gap-4">
						{currentlyReading.map((book) => (
							<BookCard key={book.link} {...book} />
						))}
					</div>
				</div>
			</section>
			<div className="mb-10">
				<hr className="dark:border-gray-600" />
			</div>
			<section className="mb-10">
				<h2 className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 pt-[4px] pb-1 font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-[22px] w-[22px] text-yellow-500">
						<Icon name="checkDouble" />
					</span>
					<span className="uppercase">Read</span>
				</h2>
				<div className="mt-4">
					<div className="grid grid-cols-2 gap-4">
						{read.map((book) => (
							<BookCard key={book.link} {...book} />
						))}
					</div>
				</div>
			</section>
			<div className="mb-10">
				<hr className="dark:border-gray-600" />
			</div>
			<section className="mb-28">
				<h2 className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 pt-[4px] pb-1 font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-5 w-5 text-blue-500">
						<Icon name="bookmark" />
					</span>
					<span className="uppercase">To Read</span>
				</h2>
				<div className="mt-4">
					<div className="grid grid-cols-2 gap-4">
						{toRead.map((book) => (
							<BookCard key={book.link} {...book} />
						))}
					</div>
				</div>
			</section>
		</div>
	)
}

ReadingList.layout = pageLayout

export const getServerSideProps: GetServerSideProps<ReadingListProps> = async ({
	res,
}) => {
	const [mustRead, currentlyReading, read, toRead] = await Promise.all([
		getGoodreadsBooks("mustRead"),
		getGoodreadsBooks("currentlyReading"),
		getGoodreadsBooks("read"),
		getGoodreadsBooks("toRead"),
	])

	res.setHeader(
		"Cache-Control",
		"public, s-maxage=86400, stale-while-revalidate=604800"
	)

	return {
		props: { mustRead, currentlyReading, read, toRead },
	}
}

export default ReadingList
