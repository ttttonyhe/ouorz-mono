import { Icon } from "@twilight-toolkit/ui"
import Head from "next/head"
import Link from "next/link"
import React from "react"
import useSWR from "swr"
import { PodcastCard, PodcastCardLoading } from "~/components/Card/Podcast"
import { pageLayout } from "~/components/Page"
import { WPPost } from "~/constants/propTypes"
import fetcher from "~/lib/fetcher"
import { NextPageWithLayout } from "~/pages/_app"
import getAPI from "~/utilities/api"

const Podcasts: NextPageWithLayout = () => {
	const { data, error } = useSWR(
		getAPI("internal", "posts", {
			perPage: 100,
			cate: 335,
			cateExclude: "5,2,74,334",
		}),
		fetcher
	)

	return (
		<div>
			<Head>
				<title>Podcasts - Tony He</title>
				<link
					rel="icon"
					href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎙️</text></svg>"
				/>
				<meta name="description" content="Podcasts that Tony's Listening to" />
			</Head>
			<section className="mt-0 pt-24 lg:mt-20 lg:pt-0">
				<div className="mb-4 flex items-center">
					<div className="flex flex-1 items-center">
						<div className="mr-4.5 mt-1 flex -rotate-6 cursor-pointer items-center">
							<span className="text-[35px] drop-shadow-lg hover:animate-spin">
								🎙️
							</span>
						</div>
						<div>
							<h2 className="flex items-center gap-x-1.5 text-[28px] font-medium tracking-wide text-black dark:text-white">
								Podcasts
							</h2>
							<p className="-mt-1 text-sm text-neutral-500 dark:text-gray-400">
								I have listened to a wide variety of audio podcasts over the
								years. Here are some of the ones that I really enjoyed.
							</p>
						</div>
					</div>
					<div className="mt-2 flex h-full items-center justify-end whitespace-nowrap">
						<div className="flex-1 pl-5 pr-3">
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
			</section>
			<div className="my-5">
				<hr className="dark:border-gray-600" />
			</div>
			<section className="mb-10 mt-4 grid grid-cols-2 gap-4 lg:grid-cols-3">
				{data && !error ? (
					data.map((podcast: WPPost) => (
						<PodcastCard
							key={podcast.post_title}
							title={podcast.title.rendered}
							description={podcast.content.rendered}
							imageURL={podcast.post_img.url}
							link={podcast.post_metas.link}
						/>
					))
				) : (
					<>
						<PodcastCardLoading uniqueKey="pc-1" />
						<PodcastCardLoading uniqueKey="pc-2" />
						<PodcastCardLoading uniqueKey="pc-3" />
					</>
				)}
			</section>
		</div>
	)
}

Podcasts.layout = pageLayout

export default Podcasts
