import { Label } from "@twilight-toolkit/ui"
import type { GetStaticPaths, GetStaticProps } from "next"
import { MDXRemote, type MDXRemoteSerializeResult } from "next-mdx-remote"
import dynamic from "next/dynamic"
import Head from "next/head"
import Link from "next/link"
import { useRouter } from "next/router"
import { useEffect, useState } from "react"
import TimeAgo from "react-timeago"

import CommentBox from "~/components/CommentBox"
import { contentLayout } from "~/components/Content"
import PostContent from "~/components/PostContent"
import SubscriptionBox from "~/components/SubscriptionBox"
import { serializeMDX } from "~/content/mdx"
import { getPostById, getPostIds, type LocalPost } from "~/content/posts"
import { useDispatch } from "~/hooks"
import useLiveViews from "~/hooks/useLiveViews"
import type { NextPageWithLayout } from "~/pages/_app"
import { setHeaderTitle } from "~/store/general/actions"
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
	/**
	 * The table of contents measures the rendered post, so it mounts a beat after
	 * the content lands and syntax highlighting has settled. Tracking which post
	 * was measured resets this when the router swaps one post for another, and
	 * covers the MDX and the HTML branch alike.
	 */
	const [renderedPostId, setRenderedPostId] = useState<string | null>(null)

	useEffect(() => {
		const id = post?.id ?? null
		const timer = setTimeout(() => setRenderedPostId(id), 100)
		return () => clearTimeout(timer)
	}, [post?.id])

	const isPostContentRendered =
		renderedPostId !== null && renderedPostId === post?.id

	useEffect(() => {
		if (!status || !post) {
			void router.replace("/404")
		}
	}, [post, router, status])

	const postId = post?.id
	const { views: postViews, isLoading: isViewsLoading } = useLiveViews(
		"post",
		postId,
		post?.post_metas.views ?? 0
	)

	useEffect(() => {
		if (!post) return
		dispatch(setHeaderTitle(post.title.rendered))
		return () => {
			dispatch(setHeaderTitle(""))
		}
	}, [dispatch, post])

	if (!post) return null

	if (!status || !post) {
		return (
			<div className="mx-auto w-1/3 animate-pulse rounded-md rounded-tl-none rounded-tr-none border border-t-0 bg-white py-3 text-center shadow-xs">
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
				className="bg-white p-5 pt-24 lg:rounded-xl lg:border lg:p-20 lg:pt-20 lg:shadow-xs dark:border-gray-800 dark:bg-gray-800">
				<div className="mb-20">
					<div className="mb-3 flex">
						<Link href={`/cate/${post.post_categories[0].term_id}`}>
							<Label type="primary" icon="cate">
								{post.post_categories[0].name}
							</Label>
						</Link>
					</div>
					<h1 className="text-1.5 leading-snug font-medium tracking-wider lg:text-post-title">
						{post.title.rendered}
					</h1>
					<p className="mt-2 flex space-x-2 text-5 tracking-wide whitespace-nowrap text-gray-500 lg:text-xl">
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
			</article>
			{isPostContentRendered && (
				<Aside key={post.id} preNext={post.post_prenext} />
			)}
			<div className="border-t border-gray-200 lg:mt-5 lg:border-none dark:border-gray-600">
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

	const shouldRenderAsHTML = /<\w+[\s\S]*>/u.test(post.content.raw)
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

export const getStaticPaths: GetStaticPaths = () => {
	const paths = getPostIds().map((id) => ({
		params: { pid: id.toString() },
	}))

	return { paths, fallback: "blocking" }
}

BlogPost.layout = contentLayout

export default BlogPost
