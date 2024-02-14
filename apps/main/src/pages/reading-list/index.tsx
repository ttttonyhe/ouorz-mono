import Head from "next/head"
import React from "react"
import useSWR from "swr"
import Link from "next/link"
import { Icon } from "@twilight-toolkit/ui"
import fetcher from "~/lib/fetcher"
import { NextPageWithLayout } from "~/pages/_app"
import { pageLayout } from "~/components/Page"
import { BookCard, BookCardLoading } from "~/components/Card/Book"
import type { Book } from "~/pages/api/goodreads"

const ReadingList: NextPageWithLayout = () => {
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
			<section className="lg:mt-20 mt-0 lg:pt-0 pt-24">
				<div className="mb-4 flex items-center">
					<div className="flex-1 flex items-center">
						<div className="flex items-center cursor-pointer mt-1 mr-4.5 -rotate-6">
							<span className="text-[35px] hover:animate-spin drop-shadow-lg">
								📚
							</span>
						</div>
						<div>
							<h2 className="font-medium text-[28px] text-black dark:text-white tracking-wide flex items-center gap-x-1.5 whitespace-nowrap">
								Reading List{" "}
								<span className="text-xs py-0.5 px-2 text-yellow-500 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-800 rounded-full border border-yellow-300 dark:border-yellow-700">
									2024
								</span>
							</h2>
							<p className="text-sm text-neutral-500 dark:text-gray-400 -mt-1">
								I{"'"}m reading or re-reading (on average) one book every month
								in 2024
							</p>
						</div>
					</div>
					<div className="h-full flex justify-end whitespace-nowrap items-center mt-2">
						<div className="flex-1 pl-5 pr-2">
							<p className="text-xl text-gray-500 dark:text-gray-400">
								<Link href="/" className="flex items-center">
									<span className="w-6 h-6 mr-2">
										<Icon name="left" />
									</span>
									Home
								</Link>
							</p>
						</div>
					</div>
				</div>
			</section>
			<div className="my-5">
				<hr className="dark:border-gray-600" />
			</div>
			<section className="mb-10">
				<label className="rounded-full bg-white dark:bg-gray-700 dark:border-gray-600 shadow-sm border border-gray-300 tracking-wider font-medium pb-1 pt-[4px] px-4 inline-flex items-center">
					<span className="w-[22px] h-[22px] flex mr-1.5 text-green-500">
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
				<label className="rounded-full bg-white dark:bg-gray-700 dark:border-gray-600 shadow-sm border border-gray-300 tracking-wider font-medium pb-1 pt-[4px] px-4 inline-flex items-center">
					<span className="w-[22px] h-[22px] flex mr-1.5 text-yellow-500">
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
				<label className="rounded-full bg-white dark:bg-gray-700 dark:border-gray-600 shadow-sm border border-gray-300 tracking-wider font-medium pb-1 pt-[4px] px-4 inline-flex items-center">
					<span className="w-5 h-5 flex mr-1.5 text-blue-500">
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
