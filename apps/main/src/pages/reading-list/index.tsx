import { Icon } from "@twilight-toolkit/ui"
import Head from "next/head"
import { useRouter } from "next/router"
import useSWR from "swr"
import { BookCard, BookCardLoading } from "~/components/Card/Book"
import { pageLayout } from "~/components/Page"
import fetcher from "~/lib/fetcher"
import type { NextPageWithLayout } from "~/pages/_app"
import type { Book } from "~/pages/api/goodreads"
import {
	getViewTransitionName,
	navigateWithTransition,
} from "~/utilities/viewTransition"

const ReadingList: NextPageWithLayout = () => {
	const router = useRouter()
	const { data: currentlyReading, error: currentlyReadingError } = useSWR(
		"api/goodreads?shelf=currentlyReading",
		fetcher
	)
	const { data: toRead, error: allError } = useSWR(
		"api/goodreads?shelf=toRead",
		fetcher
	)
	const { data: read, error: readError } = useSWR(
		"api/goodreads?shelf=read",
		fetcher
	)
	const { data: mustRead, error: mustReadError } = useSWR(
		"api/goodreads?shelf=mustRead",
		fetcher
	)

	return (
		<div>
			<Head>
				<title>Reading List - Tony He</title>
				<link
					rel="icon"
					href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📚</text></svg>"
				/>
				<meta name="description" content="Tony's GoodReads Reading List" />
			</Head>
			<section className="mt-0 pt-24 lg:mt-20 lg:pt-0">
				<div className="mb-4 flex items-center">
					<div className="flex flex-1 items-center">
						<div className="-rotate-6 mt-1 mr-4.5 flex cursor-pointer items-center">
							<span className="text-[35px] drop-shadow-lg hover:animate-spin">
								📚
							</span>
						</div>
						<div>
							<h2 className="flex items-center gap-x-1.5 whitespace-nowrap font-medium text-[28px] text-black tracking-wide dark:text-white">
								<span
									style={{
										viewTransitionName: getViewTransitionName("Reading List"),
									}}>
									Reading List
								</span>
							</h2>
							<p className="-mt-1 text-neutral-500 text-sm dark:text-gray-400">
								I{"'"}m reading or re-reading (on average) one book every month
							</p>
						</div>
					</div>
					<div className="mt-2 flex h-full items-center justify-end whitespace-nowrap">
						<div className="flex-1 pr-2 pl-5">
							<p className="text-gray-500 text-xl dark:text-gray-400">
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
				<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 pt-[4px] pb-1 font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-[22px] w-[22px] text-purple-500">
						<Icon name="bookShelf" />
					</span>
					<span className="uppercase">Curated</span>
				</label>
				<div className="mt-4">
					<div className="grid grid-cols-2 gap-4">
						{mustRead && !mustReadError ? (
							mustRead.books.map((book: Book) => (
								<BookCard key={book.title} {...book} />
							))
						) : (
							<>
								<BookCardLoading uniqueKey="cr-1" />
								<BookCardLoading uniqueKey="cr-2" />
							</>
						)}
					</div>
				</div>
			</section>
			<div className="mb-10">
				<hr className="dark:border-gray-600" />
			</div>
			<section className="mb-10">
				<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 pt-[4px] pb-1 font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-[22px] w-[22px] text-green-500">
						<Icon name="eye" />
					</span>
					<span className="uppercase">Currently Reading</span>
				</label>
				<div className="mt-4">
					<div className="grid grid-cols-2 gap-4">
						{currentlyReading && !currentlyReadingError ? (
							currentlyReading.books.map((book: Book) => (
								<BookCard key={book.title} {...book} />
							))
						) : (
							<>
								<BookCardLoading uniqueKey="cr-1" />
								<BookCardLoading uniqueKey="cr-2" />
							</>
						)}
					</div>
				</div>
			</section>
			<div className="mb-10">
				<hr className="dark:border-gray-600" />
			</div>
			<section className="mb-10">
				<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 pt-[4px] pb-1 font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-[22px] w-[22px] text-yellow-500">
						<Icon name="checkDouble" />
					</span>
					<span className="uppercase">Read</span>
				</label>
				<div className="mt-4">
					<div className="grid grid-cols-2 gap-4">
						{read && !readError ? (
							read.books.map((book: Book) => (
								<BookCard key={book.title} {...book} />
							))
						) : (
							<>
								<BookCardLoading uniqueKey="cr-1" />
								<BookCardLoading uniqueKey="cr-2" />
							</>
						)}
					</div>
				</div>
			</section>
			<div className="mb-10">
				<hr className="dark:border-gray-600" />
			</div>
			<section className="mb-28">
				<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 pt-[4px] pb-1 font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-5 w-5 text-blue-500">
						<Icon name="bookmark" />
					</span>
					<span className="uppercase">To Read</span>
				</label>
				<div className="mt-4">
					<div className="grid grid-cols-2 gap-4">
						{toRead && !allError ? (
							toRead.books.map((book: Book) => (
								<BookCard key={book.title} {...book} />
							))
						) : (
							<>
								<BookCardLoading uniqueKey="a-1" />
								<BookCardLoading uniqueKey="a-2" />
								<BookCardLoading uniqueKey="a-3" />
								<BookCardLoading uniqueKey="a-4" />
								<BookCardLoading uniqueKey="a-5" />
								<BookCardLoading uniqueKey="a-6" />
							</>
						)}
					</div>
				</div>
			</section>
		</div>
	)
}

ReadingList.layout = pageLayout

export default ReadingList
