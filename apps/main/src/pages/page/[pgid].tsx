import type { GetStaticPaths, GetStaticProps } from "next"
import Head from "next/head"
import { useRouter } from "next/router"
import { useEffect, useState } from "react"
import TimeAgo from "react-timeago"
import CommentBox from "~/components/CommentBox"
import { contentLayout } from "~/components/Content"
import PostContent from "~/components/PostContent"
import SubscriptionBox from "~/components/SubscriptionBox"
import { getPageById, getPageIds, type LocalPage } from "~/content/pages"
import { useDispatch } from "~/hooks"
import type { NextPageWithLayout } from "~/pages/_app"
import { setHeaderTitle } from "~/store/general/actions"
import getAPI from "~/utilities/api"

interface Props {
	status: boolean
	page?: LocalPage
}

const BlogPage: NextPageWithLayout = ({ status, page }: Props) => {
	const router = useRouter()
	const dispatch = useDispatch()
	const [liveViews, setLiveViews] = useState<number | null>(null)
	const [isViewsLoading, setIsViewsLoading] = useState(true)

	useEffect(() => {
		if (!status || !page) {
			router.replace("/404")
		}
	}, [page, router, status])

	useEffect(() => {
		if (!page) return
		dispatch(setHeaderTitle(page.title.rendered))
		return () => {
			dispatch(setHeaderTitle(""))
		}
	}, [dispatch, page])

	useEffect(() => {
		if (!page?.id) return
		let isActive = true
		setIsViewsLoading(true)

		const fetchViews = async () => {
			const response = await fetch(
				getAPI("internal", "page", { id: Number(page.id) }),
				{
					cache: "no-store",
				}
			)
			if (!response.ok || !isActive) return
			const data = await response.json()
			setLiveViews(Number(data?.post_metas?.views ?? 0))
			setIsViewsLoading(false)
		}

		fetchViews().catch(() => {
			if (isActive) {
				setLiveViews(page.post_metas.views ?? 0)
				setIsViewsLoading(false)
			}
		})

		const interval = window.setInterval(() => {
			fetchViews().catch(() => {})
		}, 15000)

		return () => {
			isActive = false
			window.clearInterval(interval)
		}
	}, [page?.id, page?.post_metas.views])

	if (!status || !page) {
		return (
			<div className="shadow-xs mx-auto w-1/3 animate-pulse rounded-md rounded-tl-none rounded-tr-none border border-t-0 bg-white py-3 text-center">
				<h1 className="text-lg font-medium">404 Not Found</h1>
				<p className="text-sm font-light tracking-wide text-gray-500">
					redirecting...
				</p>
			</div>
		)
	}

	const title = `${page.title.rendered} - Tony He`
	const pageViews = liveViews ?? page.post_metas.views

	return (
		<div>
			<Head>
				<title>{title}</title>
				<link
					rel="icon"
					href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📄</text></svg>"
				/>
				<meta name="description" content={page.title.rendered} />
			</Head>
			<article
				data-cy="pageContent"
				className="lg:shadow-xs bg-white p-5 pt-24 dark:border-gray-800 dark:bg-gray-800 lg:rounded-xl lg:border lg:p-20">
				<div className="mb-20">
					<h1 className="text-1.5 font-medium leading-snug tracking-wider lg:text-post-title">
						{page.title.rendered}
					</h1>
					<p className="mt-2 flex space-x-2 text-5 tracking-wide text-gray-500 dark:text-gray-400 lg:text-xl">
						<span>
							Posted <TimeAgo date={page.date} />
						</span>
						<span>·</span>
						{isViewsLoading ? (
							<span className="mt-0.5 inline-block h-6 w-16 animate-pulse rounded bg-gray-200 align-middle dark:bg-gray-600" />
						) : (
							<span>{pageViews} Views</span>
						)}
					</p>
				</div>
				<PostContent content={page.content.rendered} />
			</article>
			<div className="mt-5">
				<SubscriptionBox type="lg" />
			</div>
			<CommentBox />
		</div>
	)
}

export const getStaticProps: GetStaticProps = async (context) => {
	const pageId = Number(context.params?.pgid)
	const page = getPageById(pageId)

	if (!page) {
		return {
			props: {
				status: false,
			},
			revalidate: 60,
		}
	}

	return {
		props: {
			status: true,
			page,
		},
		revalidate: 24 * 3600,
	}
}

export const getStaticPaths: GetStaticPaths = async () => ({
	paths: getPageIds().map((id) => ({ params: { pgid: String(id) } })),
	fallback: "blocking",
})

BlogPage.layout = contentLayout

export default BlogPage
