import Head from 'next/head'
import React from 'react'
import { NextPageWithLayout } from '~/pages/_app'
import { pageLayout } from '~/components/Page'
import Link from 'next/link'
import { Icon } from '@twilight-toolkit/ui'
import PageCard from '~/components/Card/Page'

const Pages: NextPageWithLayout = () => {
	return (
		<div>
			<Head>
				<title>Pages - TonyHe</title>
				<link
					rel="icon"
					href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📑</text></svg>"
				/>
				<meta name="description" content="TonyHe's blog pages" />
			</Head>
			<div className="lg:mt-20 mt-0 lg:pt-0 pt-24">
				<div className="mb-4 flex items-center">
					<div className="flex-1 items-center">
						<h1 className="font-medium text-1 text-black dark:text-white tracking-wide">
							<span className="hover:animate-spin inline-block cursor-pointer mr-3">
								📑
							</span>
							Pages
						</h1>
					</div>
					<div className="h-full flex justify-end whitespace-nowrap items-center mt-2">
						<div className="flex-1 px-5">
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
			</div>
			<div className="glowing-area mt-5 grid grid-cols-2 gap-4">
				<PageCard
					title="Dashboard"
					des="Track my metrics"
					icon="ppt"
					className="text-blue-500"
					href="/dashboard"
				/>
				<PageCard
					title="Guestbook"
					des="Leave your comments"
					icon="chat"
					className="text-green-400"
					href="/page/249"
				/>
				<PageCard
					title="AMA"
					des="Ask me anything"
					icon="question"
					className="text-yellow-400"
					href="/page/765"
				/>
				<PageCard
					title="Links"
					des="Some of my friends"
					icon="people"
					className="text-pink-400"
					href="/friends"
				/>
				<PageCard
					title="Thoughts"
					des="Random but memorable"
					icon="lightBulb"
					className="text-red-400"
					href="https://notion.ouorz.com"
				/>
				<PageCard
					title="Analytics"
					des="Visitor statistics"
					icon="growth"
					className="text-blue-400"
					href="https://analytics.ouorz.com/share/E4O9QpCn/ouorz-next"
				/>
				<PageCard
					title="Podcast"
					des="Known Unknowns"
					icon="mic"
					className="text-gray-600"
					href="https://kukfm.com"
				/>
				<PageCard
					title="Snapod"
					des="Podcast hosting platform"
					icon="microphone"
					className="text-gray-500"
					href="https://www.snapodcast.com"
				/>
			</div>
		</div>
	)
}

Pages.layout = pageLayout

export default Pages
