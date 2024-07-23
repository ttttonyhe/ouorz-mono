import { Label } from "@twilight-toolkit/ui"
import { GetStaticPaths, GetStaticProps } from "next"
// Components
import Head from "next/head"
import Link from "next/link"
import { useRouter } from "next/router"
import redirect from "nextjs-redirect"
import { useEffect } from "react"
import TimeAgo from "react-timeago"
import Aside from "~/components/Aside"
import { CardTool } from "~/components/Card/WithImage/tool"
import CommentBox from "~/components/CommentBox"
import { contentLayout } from "~/components/Content"
import PostContent from "~/components/PostContent"
import SubscriptionBox from "~/components/SubscriptionBox"
import { useDispatch } from "~/hooks"
import { NextPageWithLayout } from "~/pages/_app"
import { setHeaderTitle } from "~/store/general/actions"
import getAPI from "~/utilities/api"
// Utilities
import { trimStr } from "~/utilities/string"

const Redirect = redirect("/404")

interface Props {
	status: boolean
	post?: any
}

const BlogPost: NextPageWithLayout = ({ status, post }: Props) => {
	const router = useRouter()
	const dispatch = useDispatch()

	if (!status || !post) {
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
				className="bg-white p-5 pt-24 dark:border-gray-800 dark:bg-gray-800 lg:rounded-xl lg:border lg:p-20 lg:pt-20 lg:shadow-sm">
				<div className="mb-20">
					<div className="mb-3 flex">
						<Link href={`/cate/${post.post_categories[0].term_id}`}>
							<Label type="primary" icon="cate">
								{post.post_categories[0].name}
							</Label>
						</Link>
					</div>
					<h1 className="text-1.5 font-medium leading-snug tracking-wider lg:text-postTitle">
						{post.title.rendered}
					</h1>
					<p className="mt-2 flex space-x-2 whitespace-nowrap text-5 tracking-wide text-gray-500 lg:text-xl">
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
				<PostContent content={post.content.rendered} />
				{post.post_categories[0].term_id === 4 && (
					<div className="mt-12">
						<CardTool item={post} preview={false} />
					</div>
				)}
			</article>
			<Aside preNext={post.post_prenext} />
			<div className="border-t border-gray-200 lg:mt-5 lg:border-none">
				<SubscriptionBox type="lg" />
			</div>
			<CommentBox />
		</div>
	)
}

export const getStaticProps: GetStaticProps = async (context) => {
	const pid = context.params.pid

	try {
		// Fetch page data
		const resData = await fetch(
			getAPI("internal", "post", {
				id: parseInt(pid as string),
			})
		)

		if (!resData.ok) {
			return {
				props: {
					status: false,
				},
				revalidate: 10,
			}
		} else {
			const postData = await resData.json()
			return {
				props: {
					status: true,
					post: postData,
				},
				revalidate: 3600 * 24,
			}
		}
	} catch (e) {
		console.error(e)
	}
}

export const getStaticPaths: GetStaticPaths = async () => {
	// get all post ids for SSG
	const res = await fetch(getAPI("internal", "allPostIDs"))

	let postIDs: number[]
	await res
		.json()
		.then((data) => {
			postIDs = data
		})
		.catch((err) => {
			console.error(err)
			console.log(res.body)
		})

	const paths = postIDs.map((id) => ({
		params: { pid: id.toString() },
	}))

	return { paths, fallback: "blocking" }
}

BlogPost.layout = contentLayout

export default BlogPost
