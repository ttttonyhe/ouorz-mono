import { GetStaticProps } from "next"
import Head from "next/head"
import React, { useEffect, useState } from "react"
// import NextJS13Preview from "~/components/Banners/NextJS13Preview"
import YearOfReformation from "~/components/Banners/YearOfReformation"
import List from "~/components/List"
import { pageLayout } from "~/components/Page"
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

	useEffect(() => {
		const greetingNumber = Math.floor(Math.random() * 10) % 3
		setGreeting(GREETINGS[greetingNumber])
	}, [])

	return (
		<>
			<Head>
				<title>Tony He</title>
			</Head>
			<div className="mt-0 pt-24 lg:mt-20 lg:pt-0">
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
