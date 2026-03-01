import { Icon } from "@twilight-toolkit/ui"
import type { GetStaticPaths, GetStaticProps } from "next"
import Head from "next/head"
import Link from "next/link"
import { useRouter } from "next/router"
import { useEffect } from "react"
import List from "~/components/List"
import { pageLayout } from "~/components/Page"
import SubscriptionBox from "~/components/SubscriptionBox"
import { getAllPosts, getCategoryById } from "~/content/posts"
import type { NextPageWithLayout } from "~/pages/_app"

interface CateProps {
	info: { status: boolean; name: string; count: number; id: number }
}

const Cate: NextPageWithLayout = ({ info }: CateProps) => {
	const title = `${info.name} - Tony He`
	const router = useRouter()

	useEffect(() => {
		if (!info.status) {
			router.replace("/404")
		}
	}, [info.status, router])

	if (info.status) {
		return (
			<div>
				<Head>
					<title>{title}</title>
					<link
						rel="icon"
						href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🗂️</text></svg>"
					/>
					<meta
						name="description"
						content={`TonyHe's content under category "${info.name}"`}
					/>
				</Head>
				<div className="mt-0 pt-24 lg:mt-20 lg:pt-0">
					<div className="mb-4 items-center lg:flex">
						<div className="flex-1 items-center">
							<h1 className="flex justify-center text-1 font-medium tracking-wide text-black dark:text-white lg:justify-start">
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
					<List.Infinite type="cate" cate={info.id} />
				</div>
			</div>
		)
	}

	return (
		<div className="shadow-xs mx-auto w-1/3 animate-pulse rounded-md rounded-tl-none rounded-tr-none border border-t-0 bg-white py-3 text-center">
			<h1 className="text-lg font-medium">404 Not Found</h1>
			<p className="text-sm font-light tracking-wide text-gray-500">
				redirecting...
			</p>
		</div>
	)
}

Cate.layout = pageLayout

export const getStaticProps: GetStaticProps = async (context) => {
	const cid = Number(context.params?.cid)
	const infoData = getCategoryById(cid)

	if (!infoData) {
		return {
			props: { info: { status: false } },
		}
	}

	return {
		props: {
			info: {
				status: true,
				name: infoData.name,
				count: infoData.count,
				id: infoData.id,
			},
		},
	}
}

export const getStaticPaths: GetStaticPaths = async () => {
	const categoryIds = Array.from(
		new Set(getAllPosts().map((post) => post.post_categories[0]?.term_id))
	).filter(Boolean)
	return {
		paths: categoryIds.map((id) => ({ params: { cid: String(id) } })),
		fallback: "blocking",
	}
}

export default Cate
