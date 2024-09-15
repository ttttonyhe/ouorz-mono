import { Icon } from "@twilight-toolkit/ui"
import { GetStaticProps } from "next"
import Head from "next/head"
import React, { useEffect, useState } from "react"
import ResearchPapers from "~/components/Banners/Research"
// import NextJS13Preview from "~/components/Banners/NextJS13Preview"
import YearOfReformation from "~/components/Banners/YearOfReformation"
import List from "~/components/List"
import { pageLayout } from "~/components/Page"
import SubscriptionBox from "~/components/SubscriptionBox"
import Top from "~/components/Top"
import { NextPageWithLayout } from "~/pages/_app"
import getAPI from "~/utilities/api"

const GREETINGS = [" there, it's Tony", ", Tony here", ", I'm Tony"]

interface Props {
	stickyNotFound: boolean
	stickyPosts: any
}

const Home: NextPageWithLayout = ({ stickyNotFound, stickyPosts }: Props) => {
	const [greeting, setGreeting] = useState(GREETINGS[0])
	const [showPosts, setShowPosts] = useState(false)

	useEffect(() => {
		const greetingNumber = Math.floor(Math.random() * 10) % 3
		setGreeting(GREETINGS[greetingNumber])
	}, [])

	return (
		<>
			<Head>
				<title>Tony He</title>
			</Head>
			<section className="mt-0 pt-24 lg:mt-20 lg:pt-0">
				<div>
					<h1 className="mb-0.5 flex items-center whitespace-nowrap text-3xl font-medium leading-14 tracking-wide text-black dark:text-white lg:text-1">
						<span className="mr-2.5 inline-block animate-waveHand cursor-pointer hover:animate-waveHandAgain">
							👋
						</span>{" "}
						Hey{greeting}
						<a
							href="https://cal.com/tonyhe/15min"
							className="effect-pressing ml-2 mt-0.5 hidden lg:block"
							target="_blank"
							rel="noreferrer">
							<span className="ml-2 flex items-center rounded-md border border-gray-400 px-2.5 py-1 text-sm tracking-normal text-gray-500 hover:border-gray-500 hover:text-gray-600 hover:shadow-sm dark:!border-white dark:text-white dark:hover:text-gray-100 dark:hover:opacity-80">
								Let&apos;s chat →
							</span>
						</a>
					</h1>
					<p className="pb-1.5 pl-1.5 pt-1 text-3 font-light leading-14 tracking-wider text-gray-500 dark:text-gray-200 lg:text-2">
						I&apos;m currently living a<del>n absolutely not</del> meaningless
						life with <del>totally not</del> unachievable goals.
					</p>
				</div>
				<Top />
			</section>
			<section className="mt-11">
				<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 pb-1 pt-[4px] font-medium tracking-wider shadow-sm dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-5 w-5 text-yellow-500">
						<Icon name="focus" />
					</span>
					<span className="uppercase">Featured</span>
				</label>
				<div className="mt-4.5">
					{/* <div className="-mt-3 border-b pb-8 dark:border-gray-700"> */}
					<ResearchPapers />
					{/* </div> */}
					<div className="mt-5">
						<YearOfReformation />
					</div>
					{/*
				<div className="mt-5">
					<NextJS13Preview />
				</div>
				*/}
				</div>
			</section>
			{/* <section className="mt-11">
				{!stickyNotFound && <List.Static posts={stickyPosts} sticky={true} />}
			</section> */}
			<section className="mt-12">
				<div className="flex justify-between">
					<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 pb-1 pt-[4px] font-medium tracking-wider shadow-sm dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5 text-blue-500">
							<Icon name="article" />
						</span>
						<span className="uppercase">Blog Posts</span>
					</label>
					<button
						onClick={() => setShowPosts(!showPosts)}
						className="effect-pressing inline-flex items-center rounded-full border border-gray-300 bg-white px-4 pb-1 pt-[4px] font-medium tracking-wider shadow-sm hover:shadow-inner dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600">
						<span
							className={`flex h-5 w-5 text-gray-400 ${showPosts ? "rotate-180" : "rotate-0"}`}>
							<Icon name="arrowUp" />
						</span>
					</button>
				</div>
				{showPosts ? (
					<div className="mt-4.5">
						<List type="index" />
					</div>
				) : (
					<div className="mt-4.5">
						<SubscriptionBox type="sm" />
					</div>
				)}
			</section>
		</>
	)
}

Home.layout = pageLayout

export const getStaticProps: GetStaticProps = async () => {
	const getStickyPostsResponse = await fetch(
		getAPI("internal", "posts", {
			sticky: true,
			perPage: 10,
			cateExclude: "5,2,74,335",
		})
	)

	const stickyPostData = await getStickyPostsResponse.json()

	return {
		props: {
			stickyNotFound: !stickyPostData,
			stickyPosts: stickyPostData,
		},
		revalidate: 3600 * 24 * 31,
	}
}

export default Home
