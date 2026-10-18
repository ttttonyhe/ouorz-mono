import { Label } from "@twilight-toolkit/ui"
import type { GetStaticPaths, GetStaticProps } from "next"
import dynamic from "next/dynamic"
import Head from "next/head"
import Link from "next/link"
import { useEffect, useState } from "react"
import TimeAgo from "react-timeago"

import CommentBox from "~/components/CommentBox"
import { contentLayout } from "~/components/Content"
import PostContent from "~/components/PostContent"
import SubscriptionBox from "~/components/SubscriptionBox"
import { renderMDX } from "~/content/mdx"
import { getPostById, getPostIds, type LocalPost } from "~/content/posts"
import { useDispatch } from "~/hooks"
import useLiveViews from "~/hooks/useLiveViews"
import type { NextPageWithLayout } from "~/pages/_app"
import { setHeaderTitle } from "~/store/general/actions"

const Aside = dynamic(() => import("~/components/Aside"), { ssr: false })

interface Props {
	post: LocalPost
	mdxHtml: string | null
}

const BlogPost: NextPageWithLayout<Props> = ({ post, mdxHtml }) => {
	const dispatch = useDispatch()
	// Measure the table of contents after post content and highlighting settle.
	const [renderedPostId, setRenderedPostId] = useState<string | null>(null)

	useEffect(() => {
		const id = post.id
		const timer = setTimeout(() => setRenderedPostId(id), 100)
		return () => clearTimeout(timer)
	}, [post.id])

	const isPostContentRendered =
		renderedPostId !== null && renderedPostId === post.id

	const { views: postViews, isLoading: isViewsLoading } = useLiveViews(
		"post",
		post.id,
		post.post_metas.views
	)

	useEffect(() => {
		dispatch(setHeaderTitle(post.title.rendered))
		return () => {
			dispatch(setHeaderTitle(""))
		}
	}, [dispatch, post])

	return (
		<div>
			<Head>
				<link
					rel="icon"
					href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📃</text></svg>"
				/>
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
					<p
						lang="en"
						className="mt-2 flex space-x-2 text-5 tracking-wide whitespace-nowrap text-gray-500 lg:text-xl">
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
					{mdxHtml !== null ? (
						<div
							className="prose max-w-none dark:prose-invert"
							dangerouslySetInnerHTML={{ __html: mdxHtml }}
						/>
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

export const getStaticProps: GetStaticProps<Props> = async (context) => {
	const pid = Number(context.params?.pid)
	const post = getPostById(pid)

	if (!post) {
		return { notFound: true }
	}

	const shouldRenderAsHTML = /<\w+[\s\S]*>/u.test(post.content.raw)
	const mdxHtml = shouldRenderAsHTML ? null : await renderMDX(post.content.raw)

	return {
		props: {
			post,
			mdxHtml,
		},
		revalidate: 24 * 3600,
	}
}

export const getStaticPaths: GetStaticPaths = () => {
	const paths = getPostIds().map((id) => ({
		params: { pid: id.toString() },
	}))

	return { paths, fallback: false }
}

BlogPost.layout = contentLayout

export default BlogPost
