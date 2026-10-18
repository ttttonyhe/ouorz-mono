import { Icon } from "@twilight-toolkit/ui"
import type { GetStaticProps } from "next"
import Head from "next/head"
import { useRouter } from "next/router"

import { PodcastCard, type PodcastCardProps } from "~/components/Card/Podcast"
import { pageLayout } from "~/components/Page"
import { getPosts } from "~/content/posts"
import type { NextPageWithLayout } from "~/pages/_app"
import {
	getViewTransitionName,
	navigateWithTransition,
} from "~/utilities/viewTransition"

interface Props {
	podcasts: PodcastCardProps[]
}

const Podcasts: NextPageWithLayout<Props> = ({ podcasts }) => {
	const router = useRouter()

	return (
		<div>
			<Head>
				<link
					rel="icon"
					href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎙️</text></svg>"
				/>
			</Head>
			<section className="mt-0 pt-24 lg:mt-20 lg:pt-0">
				<div className="mb-4 flex items-center">
					<div className="flex flex-1 items-center">
						<div className="mt-1 mr-4.5 flex -rotate-6 cursor-pointer items-center">
							<span className="text-[35px] drop-shadow-lg hover:animate-spin">
								🎙️
							</span>
						</div>
						<div>
							<h1 className="flex items-center gap-x-1.5 text-[28px] font-medium tracking-wide text-black dark:text-white">
								<span
									style={{
										viewTransitionName: getViewTransitionName("Podcasts"),
									}}>
									Podcasts
								</span>
							</h1>
							<p className="-mt-1 text-sm text-neutral-500 dark:text-gray-400">
								I have listened to a wide variety of audio podcasts over the
								years. Here are some of the ones that I really enjoyed.
							</p>
						</div>
					</div>
					<div className="mt-2 flex h-full items-center justify-end whitespace-nowrap">
						<div className="flex-1 pr-3 pl-5">
							<p className="text-xl text-gray-500 dark:text-gray-400">
								<button
									type="button"
									onClick={() => navigateWithTransition(router, "/pages")}
									className="flex cursor-pointer items-center">
									<span className="mr-2 h-6 w-6">
										<Icon name="left" />
									</span>
									Pages
								</button>
							</p>
						</div>
					</div>
				</div>
			</section>
			<div className="my-5">
				<hr className="dark:border-gray-600" />
			</div>
			<section className="mt-4 mb-10 grid grid-cols-2 gap-4 lg:grid-cols-3">
				{podcasts.map((podcast) => (
					<PodcastCard key={podcast.link} {...podcast} />
				))}
			</section>
		</div>
	)
}

Podcasts.layout = pageLayout

export const getStaticProps: GetStaticProps<Props> = () => ({
	props: {
		podcasts: getPosts({ cate: 335 }).map((post) => {
			const link = post.post_metas.link
			if (!link) throw new Error(`Podcast ${post.id} has no link`)
			return {
				title: post.title.rendered,
				description: post.content.rendered,
				imageURL: post.post_img.url,
				link,
			}
		}),
	},
})

export default Podcasts
