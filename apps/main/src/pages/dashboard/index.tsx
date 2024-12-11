import { Icon } from "@twilight-toolkit/ui"
import Head from "next/head"
import Link from "next/link"
import React from "react"
import GithubFollowerMetric from "~/components/Metrics/GithubFollowers"
import GithubStarMetric from "~/components/Metrics/GithubStars"
// import JMSMetric from "~/components/Metrics/JMS"
import NexmentMetric from "~/components/Metrics/Nexment"
import PageViewsMetric from "~/components/Metrics/PageViews"
import PostsMetric from "~/components/Metrics/Posts"
import SspaiMetric from "~/components/Metrics/Sspai"
import { pageLayout } from "~/components/Page"
import { NextPageWithLayout } from "~/pages/_app"

const Dashboard: NextPageWithLayout = () => {
	return (
		<>
			<Head>
				<title>Dashboard - Tony He</title>
				<link
					rel="icon"
					href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📊</text></svg>"
				/>
				<meta name="description" content="TonyHe's personal dashboard" />
				<meta name="robots" content="noindex" />
			</Head>
			<div className="mt-0 pt-24 lg:mt-20 lg:pt-0">
				<div className="mb-4 flex items-center">
					<div className="flex-1 items-center">
						<h1 className="text-1 font-medium tracking-wide text-black dark:text-white">
							<span className="mr-3 inline-block cursor-pointer hover:animate-spin">
								📊
							</span>
							Dashboard
						</h1>
					</div>
					<div className="mt-2 flex h-full items-center justify-end whitespace-nowrap">
						<div className="flex-1 px-5">
							<p className="text-xl text-gray-500 dark:text-gray-400">
								<Link href="/" className="flex items-center">
									<span className="mr-2 h-6 w-6">
										<Icon name="left" />
									</span>
									Home
								</Link>
							</p>
						</div>
					</div>
				</div>
				<div className="my-2 flex w-full items-center rounded-md border bg-white px-5 py-3 shadow-sm dark:border-gray-800 dark:bg-gray-800">
					<p className="items-center text-xl tracking-wide text-gray-500 dark:text-gray-400">
						Personal dashboard tracking various metrics of this website, and
						across platforms like Twitter, GitHub, and more.
					</p>
				</div>
			</div>
			<div
				className="glowing-area mb-10 mt-5 grid gap-4 lg:grid-cols-2"
				data-cy="metricCards">
				<GithubStarMetric />
				<GithubFollowerMetric />
			</div>
			<div className="my-2 flex w-full items-center rounded-md border bg-white px-5 py-3 shadow-sm dark:border-gray-800 dark:bg-gray-800">
				<p className="items-center text-xl tracking-wide text-gray-500 dark:text-gray-400">
					For detailed data analytics, see:{" "}
					<a
						className="font-medium text-gray-700 dark:text-gray-200"
						href="https://analytics.ouorz.com/share/E4O9QpCn/ouorz-next"
						target="_blank"
						rel="noreferrer">
						ouorz-analytics →
					</a>
				</p>
			</div>
			<div
				className="glowing-area mb-28 mt-5 grid gap-4 lg:grid-cols-2"
				data-cy="metricCards">
				<NexmentMetric />
				<SspaiMetric />
				<PostsMetric />
				<PageViewsMetric />
			</div>
		</>
	)
}

Dashboard.layout = pageLayout

export default Dashboard
