import type { GetStaticPaths, GetStaticProps } from "next"
import Head from "next/head"
import { useRouter } from "next/router"
import { useEffect } from "react"
import TimeAgo from "react-timeago"

import CommentBox from "~/components/CommentBox"
import { contentLayout } from "~/components/Content"
import PostContent from "~/components/PostContent"
import SubscriptionBox from "~/components/SubscriptionBox"
import { getPageById, getPageIds, type LocalPage } from "~/content/pages"
import { useDispatch } from "~/hooks"
import useLiveViews from "~/hooks/useLiveViews"
import type { NextPageWithLayout } from "~/pages/_app"
import { setHeaderTitle } from "~/store/general/actions"

interface Props {
	status: boolean
	page?: LocalPage
}

const BlogPage: NextPageWithLayout = ({ status, page }: Props) => {
	const router = useRouter()
	const dispatch = useDispatch()
	const { views: pageViews, isLoading: isViewsLoading } = useLiveViews(
		"page",
		page?.id,
		page?.post_metas.views ?? 0
	)

	useEffect(() => {
		if (!status || !page) {
			void router.replace("/404")
		}
	}, [page, router, status])

	useEffect(() => {
		if (!page) return
		dispatch(setHeaderTitle(page.title.rendered))
		return () => {
			dispatch(setHeaderTitle(""))
		}
	}, [dispatch, page])

	if (!status || !page) {
		return (
			<div className="mx-auto w-1/3 animate-pulse rounded-md rounded-tl-none rounded-tr-none border border-t-0 bg-white py-3 text-center shadow-xs">
				<h1 className="text-lg font-medium">404 Not Found</h1>
				<p className="text-sm font-light tracking-wide text-gray-500">
					redirecting...
				</p>
			</div>
		)
	}

	const title = `${page.title.rendered} - Tony He`

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
				className="bg-white p-5 pt-24 lg:rounded-xl lg:border lg:p-20 lg:shadow-xs dark:border-gray-800 dark:bg-gray-800">
				<div className="mb-20">
					<h1 className="text-1.5 leading-snug font-medium tracking-wider lg:text-post-title">
						{page.title.rendered}
					</h1>
					<p className="mt-2 flex space-x-2 text-5 tracking-wide text-gray-500 lg:text-xl dark:text-gray-400">
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

export const getStaticProps: GetStaticProps = (context) => {
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

export const getStaticPaths: GetStaticPaths = () => ({
	paths: getPageIds().map((id) => ({ params: { pgid: String(id) } })),
	fallback: "blocking",
})

BlogPage.layout = contentLayout

export default BlogPage
