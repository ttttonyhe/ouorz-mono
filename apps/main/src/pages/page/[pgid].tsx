import { GetServerSideProps } from "next"
import Head from "next/head"
import { useRouter } from "next/router"
import redirect from "nextjs-redirect"
import { useEffect } from "react"
import TimeAgo from "react-timeago"
import CommentBox from "~/components/CommentBox"
import { contentLayout } from "~/components/Content"
import PostContent from "~/components/PostContent"
import SubscriptionBox from "~/components/SubscriptionBox"
import { useDispatch } from "~/hooks"
import { NextPageWithLayout } from "~/pages/_app"
import { setHeaderTitle } from "~/store/general/actions"
import getAPI from "~/utilities/api"

const Redirect = redirect("/404")

interface Props {
	status: boolean
	page?: any
}

const BlogPage: NextPageWithLayout = ({ status, page }: Props) => {
	const router = useRouter()
	const dispatch = useDispatch()

	if (!status || !page) {
		return (
			<Redirect>
				<div className="mx-auto w-1/3 animate-pulse rounded-md rounded-tl-none rounded-tr-none border border-t-0 bg-white py-3 text-center shadow-sm">
					<h1 className="text-lg font-medium">404 Not Found</h1>
					<p className="text-sm font-light tracking-wide text-gray-500">
						redirecting...
					</p>
				</div>
			</Redirect>
		)
	}

	const { pgid } = router.query
	const title = `${page.title.rendered} - Tony He`

	useEffect(() => {
		dispatch(setHeaderTitle(page.title.rendered))

		return () => {
			dispatch(setHeaderTitle(""))
		}
	}, [pgid])

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
				className="bg-white p-5 pt-24 dark:border-gray-800 dark:bg-gray-800 lg:rounded-xl lg:border lg:p-20 lg:shadow-sm">
				<div className="mb-20">
					<h1 className="text-1.5 font-medium leading-snug tracking-wider lg:text-postTitle">
						{page.title.rendered}
					</h1>
					<p className="mt-2 flex space-x-2 text-5 tracking-wide text-gray-500 dark:text-gray-400 lg:text-xl">
						<span>
							Posted <TimeAgo date={page.date} />
						</span>
						<span>·</span>
						<span>{page.post_metas.views} Views</span>
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

export const getServerSideProps: GetServerSideProps = async (context) => {
	const pgid = context.params.pgid

	try {
		// Increase page views
		fetch(
			getAPI("internal", "visit", {
				id: parseInt(pgid as string),
			})
		)

		// Fetch page data
		const resData = await fetch(
			getAPI("internal", "page", {
				id: parseInt(pgid as string),
			})
		)

		if (!resData.ok) {
			return {
				props: {
					status: false,
				},
			}
		} else {
			const pageData = await resData.json()
			return {
				props: {
					status: true,
					page: pageData,
				},
			}
		}
	} catch (e) {
		console.error(e)
	}
}

BlogPage.layout = contentLayout

export default BlogPage
