import Head from 'next/head'
import React from 'react'
import useSWR from 'swr'
import Link from 'next/link'
import { Icon } from '@twilight-toolkit/ui'
import fetcher from '~/lib/fetcher'
import { NextPageWithLayout } from '~/pages/_app'
import { pageLayout } from '~/components/Page'
import getApi from '~/utilities/api'
import { PodcastCard, PodcastCardLoading } from '~/components/Card/Podcast'
import { WPPost } from '~/constants/propTypes'

const Podcasts: NextPageWithLayout = () => {
	const { data, error } = useSWR(
		getApi({
			perPage: 100,
			cate: '335',
			cateExclude: '5,2,74,334',
		}),
		fetcher
	)

	return (
		<div>
			<Head>
				<title>Podcasts - TonyHe</title>
				<link
					rel="icon"
					href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎙️</text></svg>"
				/>
				<meta name="description" content="Podcasts that Tony's Listening to" />
			</Head>
			<section className="lg:mt-20 mt-0 lg:pt-0 pt-24">
				<div className="mb-4 flex items-center">
					<div className="flex-1 flex items-center">
						<div className="flex items-center cursor-pointer mt-1 mr-4.5 -rotate-6">
							<i className="text-[35px] hover:animate-spin drop-shadow-lg">
								🎙️
							</i>
						</div>
						<div>
							<h2 className="font-medium text-[28px] text-black dark:text-white tracking-wide flex items-center gap-x-1.5">
								Podcasts
							</h2>
							<p className="text-sm text-neutral-500 dark:text-gray-400 -mt-1">
								Conversations spark creativity and insight
							</p>
						</div>
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
			</section>
			<div className="my-5">
				<hr className="dark:border-gray-600" />
			</div>
			<section className="mb-10 mt-4 grid grid-cols-3 gap-4">
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
