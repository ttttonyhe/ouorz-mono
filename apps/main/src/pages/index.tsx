import { Icon } from "@twilight-toolkit/ui"
import { GetStaticProps } from "next"
import Head from "next/head"
import Link from "next/link"
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

const Emphasis = ({
	name,
	className,
	children,
}: {
	name: string
	className?: string
	children?: React.ReactNode
}) => (
	<span
		className={`${className || ""} inline-flex items-center gap-x-2 rounded-md border border-gray-300 bg-white px-[8px] py-1 text-sm font-normal tracking-normal dark:border-gray-600 dark:bg-gray-700`}>
		{children ? (
			<>
				<span className="border-r border-gray-300 pr-2 dark:border-gray-600">
					{name}
				</span>
				<span>{children}</span>
			</>
		) : (
			<span>{name}</span>
		)}
	</span>
)

interface Props {
	stickyNotFound: boolean
	stickyPosts: any
}

const Home: NextPageWithLayout = ({
	stickyNotFound: _1,
	stickyPosts: _2,
}: Props) => {
	const [_greeting, setGreeting] = useState(GREETINGS[0])
	const [showPosts, setShowPosts] = useState(false)

	useEffect(() => {
		const greetingNumber = Math.floor(Math.random() * 10) % 3
		setGreeting(GREETINGS[greetingNumber])
	}, [])

	return (
		<>
			<Head>
				<title>Tony (Lipeng) He</title>
			</Head>
			<section className="mt-0 pt-24 lg:mt-20 lg:pt-0">
				<div>
					<h1 className="mb-3 flex items-center whitespace-nowrap text-3xl font-medium leading-14 tracking-wide text-black dark:text-white lg:text-1">
						<span className="mr-2.5 inline-block animate-waveHand cursor-pointer hover:animate-waveHandAgain">
							👋
						</span>
						Hello, and welcome!
					</h1>
					<div className="flex flex-col gap-y-1.5 pb-1.5 pl-1.5 pt-1 text-3 font-light leading-14 tracking-wide text-gray-500 dark:text-gray-300 lg:text-2">
						<p>
							My name is Lipeng He{" "}
							<Emphasis name="Preferred First Name">
								<b>Tony</b>
							</Emphasis>
							, and I am currently an undergraduate student and researcher with
							the{" "}
							<Emphasis
								name="University of Waterloo"
								className="border-l-4 !border-l-yellow-300">
								<Link href="https://uwaterloo.ca" target="_blank">
									<span className="flex h-4.5 w-4.5">
										<Icon name="externalLink" />
									</span>
								</Link>
							</Emphasis>{" "}
							.
						</p>
						<p>
							I was previously a{" "}
							<Emphasis
								name="Full Stack Software Engineer"
								className="hidden border-l-4 !border-l-blue-400 lg:inline-flex">
								Intern
							</Emphasis>
							<span className="lg:hidden">Full Stack Software Engineer</span> at
							various technology startups based in Toronto, Canada 🇨🇦.
						</p>
					</div>
				</div>
			</section>
			<section className="mt-11">
				<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 pb-1 pt-[4px] font-medium tracking-wider shadow-sm dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-5 w-5 text-pink-500">
						<Icon name="flag" />
					</span>
					<span className="uppercase">Featured Content</span>
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
			<section className="mt-12">
				<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 pb-1 pt-[4px] font-medium tracking-wider shadow-sm dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-5 w-5 text-purple-500">
						<Icon name="microscope" />
					</span>
					<span className="uppercase">Research Interests</span>
				</label>
				<div className="mt-4.5 flex flex-col gap-y-2 pl-1 text-3 font-light leading-14 tracking-wide text-gray-500 underline-offset-[6px] dark:text-gray-300 lg:text-[17px]">
					<p>
						<span>
							I am interested in both the theoretical and applied aspects of{" "}
							<u className="decoration-gray-300">Cryptography</u>
						</span>
						<span>
							{" "}
							and its applications throughout and beyond computing & data
							sciences.
						</span>
					</p>
					<p>
						In my previous research experience, I worked on developing and
						analyzing{" "}
						<u className="decoration-gray-300">
							Cryptographic Systems and Protocols
						</u>{" "}
						that address issues related to:
					</p>
					<div className="-ml-1 flex flex-col items-center justify-between gap-y-2 pb-4.5 pr-1 pt-5 text-sm lg:flex-row">
						<div className="text-normal flex w-full items-center gap-x-2 rounded-md border bg-white px-4 py-[7px] font-medium shadow-sm dark:border-gray-600 dark:bg-gray-700 lg:w-auto">
							<div className="flex h-5 w-5 items-center justify-center rounded-full bg-purple-400 text-white dark:bg-purple-700">
								1
							</div>
							<div className="font-bold">Privacy-preserving computing</div>
						</div>
						<div className="hidden text-3 lg:block">and</div>
						<div className="text-normal flex w-full items-center gap-x-2 rounded-md border bg-white px-4 py-[7px] font-medium shadow-sm dark:border-gray-600 dark:bg-gray-700 lg:w-auto">
							<div className="flex h-5 w-5 items-center justify-center rounded-full bg-purple-400 text-white dark:bg-purple-700">
								2
							</div>
							<div>Software security and usability</div>
						</div>
					</div>
					<p>
						My most recent research work has put an emphasis on Fully
						Homomorphic Encryption (FHE) and Privacy-preserving Machine Learning
						(PPML).
					</p>
				</div>
			</section>
			<section className="mt-12">
				<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 pb-1 pt-[4px] font-medium tracking-wider shadow-sm dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-5 w-5 text-green-500">
						<Icon name="global" />
					</span>
					<span className="uppercase">How to Reach Me</span>
				</label>
				<div className="mt-4.5">
					<Top />
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
						data-cy="showIndexPosts"
						onClick={() => setShowPosts(!showPosts)}
						className="effect-pressing inline-flex items-center rounded-full border border-gray-300 bg-white px-4 pb-1 pt-[4px] font-medium tracking-wider shadow-sm hover:shadow-inner dark:border-gray-600 dark:bg-transparent dark:text-gray-500 dark:hover:bg-gray-800">
						<span
							className={`flex h-5 w-5 duration-200 ${showPosts ? "rotate-180" : "rotate-0"}`}>
							<Icon name="arrowUp" />
						</span>
					</button>
				</div>
				{showPosts ? (
					<div className="mt-4.5 animate-appear">
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
