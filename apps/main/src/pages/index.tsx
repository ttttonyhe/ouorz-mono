import { GetStaticProps } from "next"
import Head from "next/head"
import React, { useEffect, useState } from "react"
import { NextPageWithLayout } from "~/pages/_app"
import { pageLayout } from "~/components/Page"
import List from "~/components/List"
import Top from "~/components/Top"
import getAPI from "~/utilities/api"
// import NextJS13Preview from "~/components/Banners/NextJS13Preview"
import YearOfReformation from "~/components/Banners/YearOfReformation"

const GREETINGS = [" there, it's Tony", ", Tony here", ", I'm Tony"]

interface Props {
	stickyNotFound: boolean
	stickyPosts: any
}

const Home: NextPageWithLayout = ({ stickyNotFound, stickyPosts }: Props) => {
	const [greeting, setGreeting] = useState(GREETINGS[0])

	useEffect(() => {
		const greetingNumber = Math.floor(Math.random() * 10) % 3
		setGreeting(GREETINGS[greetingNumber])
	}, [])

	return (
		<>
			<Head>
				<title>Tony He</title>
			</Head>
			<div className="lg:mt-20 mt-0 lg:pt-0 pt-24">
				<div>
					<h1 className="flex items-center font-medium text-3xl leading-14 lg:text-1 text-black dark:text-white tracking-wide mb-0.5 whitespace-nowrap">
						<span className="animate-waveHand hover:animate-waveHandAgain inline-block cursor-pointer mr-2.5">
							👋
						</span>{" "}
						Hey{greeting}
						<a
							href="https://cal.com/tonyhe/15min"
							className="effect-pressing ml-2 mt-0.5 hidden lg:block"
							target="_blank"
							rel="noreferrer"
						>
							<span className="text-sm flex items-center ml-2 py-1 px-2.5 border border-gray-400 hover:shadow-sm hover:border-gray-500 hover:text-gray-600 text-gray-500 dark:text-white dark:hover:text-gray-100 dark:!border-white dark:hover:opacity-80 rounded-md tracking-normal">
								Let&apos;s chat →
							</span>
						</a>
					</h1>
					<p className="text-3 lg:text-2 text-gray-500 dark:text-gray-200 leading-14 tracking-wider font-light pl-1.5 pb-1.5 pt-1">
						I&apos;m currently living a<del>n absolutely not</del> meaningless
						life with <del>totally not</del> unachievable goals.
					</p>
				</div>
				<Top />
			</div>
			<div className="mt-10">
				{!stickyNotFound && <List.Static posts={stickyPosts} sticky={true} />}
			</div>
			{/* <div className="mt-5">
				<NextJS13Preview />
			</div> */}
			<div className="mt-5">
				<YearOfReformation />
			</div>
			<div className="mt-5">
				<List type="index" />
			</div>
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
