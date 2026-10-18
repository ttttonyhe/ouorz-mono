import { Icon } from "@twilight-toolkit/ui"
import type { GetStaticPaths, GetStaticProps } from "next"
import Head from "next/head"
import Link from "next/link"

import List from "~/components/List"
import { pageLayout } from "~/components/Page"
import SubscriptionBox from "~/components/SubscriptionBox"
import {
	getAllPosts,
	getCategoryById,
	getPosts,
	type LocalPost,
} from "~/content/posts"
import type { NextPageWithLayout } from "~/pages/_app"

interface CateProps {
	info: { name: string; count: number; id: number; noindex: boolean }
	posts: LocalPost[]
}

const Cate: NextPageWithLayout<CateProps> = ({ info, posts }) => {
	return (
		<div>
			<Head>
				<link
					rel="icon"
					href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🗂️</text></svg>"
				/>
			</Head>
			<div className="mt-0 pt-24 lg:mt-20 lg:pt-0">
				<div className="mb-4 items-center lg:flex">
					<div className="flex-1 items-center">
						<h1 className="flex justify-center text-1 font-medium tracking-wide text-black lg:justify-start dark:text-white">
							<span className="mr-3 inline-block cursor-pointer hover:animate-spin">
								🗂️
							</span>
							<span data-cy="cateName">{info.name}</span>
						</h1>
					</div>
					<div className="mt-2 flex h-full items-center justify-center whitespace-nowrap lg:justify-end">
						<div className="border-r border-r-gray-200 px-5 lg:flex-1 lg:text-center">
							<p className="flex items-center text-xl text-gray-500 dark:text-gray-400">
								<span className="mr-2 h-6 w-6">
									<Icon name="count" />
								</span>
								{info.count} posts
							</p>
						</div>
						<div className="px-5 lg:flex-1">
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
				<SubscriptionBox type="sm" />
			</div>
			<div className="mt-10 lg:mt-5">
				<List.Infinite
					key={info.id}
					type="cate"
					cate={info.id}
					initialPosts={posts}
				/>
			</div>
		</div>
	)
}

Cate.layout = pageLayout

export const getStaticProps: GetStaticProps<CateProps> = (context) => {
	const id = Number(context.params?.cid)
	const info = getCategoryById(id)
	if (!info) return { notFound: true }
	return {
		props: {
			info,
			posts: getPosts({ cate: id, perPage: 10 }),
		},
	}
}

export const getStaticPaths: GetStaticPaths = () => ({
	paths: [
		...new Set(getAllPosts().map((post) => post.post_categories[0].term_id)),
	].map((id) => ({ params: { cid: String(id) } })),
	fallback: false,
})

export default Cate
