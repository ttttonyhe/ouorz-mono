import { Label } from "@twilight-toolkit/ui"
import type { GetStaticPaths, GetStaticProps } from "next"
import { MDXRemote, type MDXRemoteSerializeResult } from "next-mdx-remote"
import dynamic from "next/dynamic"
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
import { serializeMDX } from "~/content/mdx"
import { getPostById, getPostIds, type LocalPost } from "~/content/posts"
import { useDispatch } from "~/hooks"
import type { NextPageWithLayout } from "~/pages/_app"
import { setHeaderTitle } from "~/store/general/actions"
import getAPI from "~/utilities/api"
import { trimStr } from "~/utilities/string"

const Aside = dynamic(() => import("~/components/Aside"), { ssr: false })

interface Props {
	status: boolean
	post?: LocalPost
	mdxSource?: MDXRemoteSerializeResult | null
}

const BlogPost: NextPageWithLayout = ({ status, post, mdxSource }: Props) => {
	const router = useRouter()
	const dispatch = useDispatch()
	const [isPostContentRendered, setIsPostContentRendered] = useState(false)
	const [liveViews, setLiveViews] = useState<number | null>(null)
	const [isViewsLoading, setIsViewsLoading] = useState(true)

	useEffect(() => {
		setIsPostContentRendered(true)
	}, [])

	useEffect(() => {
		if (!status || !post) {
			router.replace("/404")
		}
	}, [post, router, status])

	const postId = post?.id

	useEffect(() => {
		if (!post) return
		dispatch(setHeaderTitle(post.title.rendered))
		return () => {
			dispatch(setHeaderTitle(""))
		}
	}, [dispatch, post])

	useEffect(() => {
		if (!postId) return
		let isActive = true
		setIsViewsLoading(true)

		const fetchViews = async () => {
			const response = await fetch(
				getAPI("internal", "post", { id: Number(postId) }),
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
				setLiveViews(post?.post_metas.views ?? 0)
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
	}, [post?.post_metas.views, postId])

	if (!post) return null

	const postViews = liveViews ?? post.post_metas.views

	if (!status || !post) {
		return (
			<div className="shadow-xs mx-auto w-1/3 animate-pulse rounded-md rounded-tl-none rounded-tr-none border border-t-0 bg-white py-3 text-center">
				<h1 className="text-lg font-medium">404 Not Found</h1>
				<p className="text-sm font-light tracking-wide text-gray-500">
					redirecting...
				</p>
			</div>
		)
	}

	const title = `${post.title.rendered} - Tony He`

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
				className="lg:shadow-xs bg-white p-5 pt-24 dark:border-gray-800 dark:bg-gray-800 lg:rounded-xl lg:border lg:p-20 lg:pt-20">
				<div className="mb-20">
					<div className="mb-3 flex">
						<Link href={`/cate/${post.post_categories[0].term_id}`}>
							<Label type="primary" icon="cate">
								{post.post_categories[0].name}
							</Label>
						</Link>
					</div>
					<h1 className="text-1.5 font-medium leading-snug tracking-wider lg:text-post-title">
						{post.title.rendered}
					</h1>
					<p className="mt-2 flex space-x-2 whitespace-nowrap text-5 tracking-wide text-gray-500 lg:text-xl">
						<span>
							Posted <TimeAgo date={post.date} />
						</span>
						<span>·</span>
						{isViewsLoading ? (
							<span className="mt-0.5 inline-block h-6 w-16 animate-pulse rounded bg-gray-200 align-middle dark:bg-gray-600" />
						) : (
							<span>{postViews} Views</span>
						)}
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
				<div className="blog-content">
					{mdxSource ? (
						<div className="prose max-w-none dark:prose-invert">
							<MDXRemote {...mdxSource} />
						</div>
					) : (
						<PostContent content={post.content.rendered} />
					)}
				</div>
				{post.post_categories[0].term_id === 4 && (
					<div className="mt-12">
						<CardTool item={post as any} preview={false} />
					</div>
				)}
			</article>
			{isPostContentRendered && <Aside preNext={post.post_prenext} />}
			<div className="border-t border-gray-200 dark:border-gray-600 lg:mt-5 lg:border-none">
				<SubscriptionBox type="lg" />
			</div>
			<CommentBox />
		</div>
	)
}

export const getStaticProps: GetStaticProps = async (context) => {
	const pid = Number(context.params?.pid)
	const post = getPostById(pid)

	if (!post) {
		return {
			props: {
				status: false,
			},
			revalidate: 10,
		}
	}

	const shouldRenderAsHTML = /<\w+[\s\S]*>/.test(post.content.raw)
	let mdxSource: MDXRemoteSerializeResult | null = null

	if (!shouldRenderAsHTML) {
		try {
			mdxSource = await serializeMDX(post.content.raw)
		} catch {
			mdxSource = null
		}
	}

	return {
		props: {
			status: true,
			post: {
				...post,
			},
			mdxSource,
		},
		revalidate: 24 * 3600,
	}
}

export const getStaticPaths: GetStaticPaths = async () => {
	const paths = getPostIds().map((id) => ({
		params: { pid: id.toString() },
	}))

	return { paths, fallback: "blocking" }
}

BlogPost.layout = contentLayout

export default BlogPost
