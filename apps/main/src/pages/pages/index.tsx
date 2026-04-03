import { Icon } from "@twilight-toolkit/ui"
import Head from "next/head"
import Link from "next/link"
import PageCard from "~/components/Card/Page"
import { pageLayout } from "~/components/Page"
import type { NextPageWithLayout } from "~/pages/_app"
import { getViewTransitionName } from "~/utilities/viewTransition"

const Pages: NextPageWithLayout = () => {
	return (
		<div>
			<Head>
				<title>Pages - Tony He</title>
				<link
					rel="icon"
					href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📑</text></svg>"
				/>
				<meta name="description" content="TonyHe's blog pages" />
			</Head>
			<div className="mt-0 pt-24 lg:mt-20 lg:pt-0">
				<div className="mb-4 flex items-center">
					<div className="flex-1 items-center">
						<h1 className="text-1 font-medium tracking-wide text-black dark:text-white">
							<span className="mr-3 inline-block cursor-pointer hover:animate-spin">
								📑
							</span>
							Pages
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
			</div>
			<div className="mt-5 flex flex-col gap-y-10">
				<div className="glowing-area grid grid-cols-2 gap-4">
					<PageCard
						title="Dashboard"
						des="Track my metrics"
						icon="ppt"
						className="text-blue-500"
						href="/dashboard"
						viewTransitionName={getViewTransitionName("Dashboard")}
					/>
					<PageCard
						title="Web 3.0"
						des="Wallets, identities and assets"
						icon="rainbow"
						className="text-pink-500"
						href="/web3"
						viewTransitionName={getViewTransitionName("Web 3.0")}
					/>
					<PageCard
						title="Reading List"
						des="My book shelf"
						icon="bookmark"
						className="text-green-500"
						href="/reading-list"
						viewTransitionName={getViewTransitionName("Reading List")}
					/>
					<PageCard
						title="Podcasts"
						des="My recommendations"
						icon="mic"
						className="text-yellow-500"
						href="/podcasts"
						viewTransitionName={getViewTransitionName("Podcasts")}
					/>
					<PageCard
						title="Guestbook"
						des="Leave your comments"
						icon="question"
						className="text-gray-400"
						href="/page/249"
					/>
					<PageCard
						title="Links"
						des="A collection of fun stuff"
						icon="links"
						className="text-gray-400"
						href="/links"
						viewTransitionName={getViewTransitionName("Links")}
					/>
					<PageCard
						title="Analytics"
						des="Website statistics"
						icon="growth"
						className="text-gray-400"
						href="https://analytics.ouorz.com/share/E4O9QpCn/ouorz-next"
					/>
					<PageCard
						title="Known Unknowns"
						des="Podcast"
						icon="microphone"
						className="text-gray-400"
						href="https://kukfm.com"
					/>
				</div>
				<hr className="dark:border-gray-600" />
				<div className="glowing-area grid grid-cols-2 gap-4">
					<PageCard
						title="Lune Research"
						des="AI-native research workspace"
						icon="https://luneresearch.com/lune-logo.svg"
						href="https://luneresearch.com"
					/>
					<PageCard
						title="Snapod"
						des="PaaS for podcasters"
						icon="🎙️"
						href="https://www.snapodcast.com"
					/>
					<PageCard
						title="Last Week in Agents"
						des="RSS feed for top-tier papers"
						icon="rss"
						className="text-yellow-500"
						href="https://openclaw.lipeng.ac/feeds/last-week-in-agents.xml"
					/>
					<PageCard
						title="Autogrind"
						des="24x7 auto-work mode skill"
						icon="✊"
						href="https://github.com/ttttonyhe/autogrind"
					/>
				</div>
			</div>
		</div>
	)
}

Pages.layout = pageLayout

export default Pages
