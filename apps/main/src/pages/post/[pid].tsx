import { Label } from "@twilight-toolkit/ui"
import type { GetStaticPaths, GetStaticProps } from "next"
import dynamic from "next/dynamic"
// Components
import Head from "next/head"
import Link from "next/link"
import { useRouter } from "next/router"
import { useEffect, useState } from "react"
import TimeAgo from "react-timeago"
import { CardTool } from "~/components/Card/WithImage/tool"
import CommentBox from "~/components/CommentBox"
import { contentLayout } from "~/components/Content"
import PostContent from "~/components/PostContent"
import SubscriptionBox from "~/components/SubscriptionBox"
import { useDispatch } from "~/hooks"
import type { NextPageWithLayout } from "~/pages/_app"
import { setHeaderTitle } from "~/store/general/actions"
import getAPI from "~/utilities/api"
// Utilities
import { trimStr } from "~/utilities/string"

const Aside = dynamic(() => import("~/components/Aside"), { ssr: false })

interface Props {
	status: boolean
	post?: any
}

const BlogPost: NextPageWithLayout = ({ status, post }: Props) => {
	const router = useRouter()
	const dispatch = useDispatch()
	const [isPostContentRendered, setIsPostContentRendered] = useState(false)

	if (!status || !post) {
		useEffect(() => {
			router.replace("/404")
		}, [])

		return (
			<div className="mx-auto w-1/3 animate-pulse rounded-md rounded-tl-none rounded-tr-none border border-t-0 bg-white py-3 text-center shadow-xs">
				<h1 className="font-medium text-lg">404 Not Found</h1>
				<p className="font-light text-gray-500 text-sm tracking-wide">
					redirecting...
				</p>
			</div>
		)
	}

	const { pid } = router.query
	const title = `${post.title.rendered} - Tony He`

	useEffect(() => {
		dispatch(setHeaderTitle(post.title.rendered))
		fetch(getAPI("internal", "visit"), {
			method: "POST",
			body: JSON.stringify({
				id: pid,
			}),
		}).catch((err) => {
			console.error(err)
		})
		return () => {
			dispatch(setHeaderTitle(""))
		}
	}, [pid])

	return (
		<div>
			<Head>
				<title>{title}</title>
				<link
					rel="icon"
					href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📃</text></svg>"
				/>
				<meta
					name="description"
					content={trimStr(post.post_excerpt.four, 150)}
				/>
				{post.post_img.url && (
					<meta property="og:image" content={post.post_img.url} />
				)}
			</Head>
			<article
				data-cy="postContent"
				className="bg-white p-5 pt-24 lg:rounded-xl lg:border lg:p-20 lg:pt-20 lg:shadow-xs dark:border-gray-800 dark:bg-gray-800">
				<div className="mb-20">
					<div className="mb-3 flex">
						<Link href={`/cate/${post.post_categories[0].term_id}`}>
							<Label type="primary" icon="cate">
								{post.post_categories[0].name}
							</Label>
						</Link>
					</div>
					<h1 className="font-medium text-1.5 leading-snug tracking-wider lg:text-post-title">
						{post.title.rendered}
					</h1>
					<p className="mt-2 flex space-x-2 whitespace-nowrap text-5 text-gray-500 tracking-wide lg:text-xl">
						<span>
							Posted <TimeAgo date={post.date} />
						</span>
						<span>·</span>
						<span>{post.post_metas.views} Views</span>
						<span>·</span>
						<span className="group cursor-pointer">
							<span className="group-hover:hidden">
								{post.post_metas.reading.word_count} Words
							</span>
							<span className="hidden group-hover:block">
								<abbr title="Estimated reading time">
									ERT {post.post_metas.reading.time_required} min
								</abbr>
							</span>
						</span>
					</p>
				</div>
				<PostContent
					content={post.content.rendered}
					onRendered={() => setIsPostContentRendered(true)}
				/>
				{post.post_categories[0].term_id === 4 && (
					<div className="mt-12">
						<CardTool item={post} preview={false} />
					</div>
				)}
			</article>
			{isPostContentRendered && <Aside preNext={post.post_prenext} />}
			<div className="border-gray-200 border-t lg:mt-5 lg:border-none dark:border-gray-600">
				<SubscriptionBox type="lg" />
			</div>
			<CommentBox />
		</div>
	)
}

export const getStaticProps: GetStaticProps = async (context) => {
	const pid = context.params.pid

	try {
		const resData = await fetch(
			getAPI("internal", "post", {
				id: parseInt(pid as string, 10),
			})
		)

		if (!resData.ok) {
			return {
				props: {
					status: false,
				},
				revalidate: 10,
			}
		}

		const postData = await resData.json()

		if (!postData || Object.keys(postData).length === 0) {
			return {
				props: {
					status: false,
				},
				revalidate: 10,
			}
		}

		return {
			props: {
				status: true,
				post: postData,
			},
			revalidate: 3600 * 24,
		}
	} catch (error) {
		console.error("Failed to fetch post data:", error)
		return {
			props: {
				status: false,
			},
			revalidate: 10,
		}
	}
}

export const getStaticPaths: GetStaticPaths = async () => {
	try {
		const res = await fetch(getAPI("internal", "allPostIDs"))

		if (!res.ok) {
			return { paths: [], fallback: "blocking" }
		}

		const postIDs: number[] = await res.json()

		if (!Array.isArray(postIDs)) {
			return { paths: [], fallback: "blocking" }
		}

		const paths = postIDs.map((id) => ({
			params: { pid: id.toString() },
		}))

		return { paths, fallback: "blocking" }
	} catch (error) {
		console.error("Failed to fetch post IDs:", error)
		return { paths: [], fallback: "blocking" }
	}
}

BlogPost.layout = contentLayout

export default BlogPost
