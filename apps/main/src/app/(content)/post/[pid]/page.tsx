import { redirect } from 'next/navigation'
import Link from 'next/link'
import TimeAgo from '~/components/TimeAgo'
import SubscriptionBox from '~/components/SubscriptionBox'
// import CommentBox from '~/components/CommentBox'
import PostContent from '~/components/PostContent'
import { Label } from '@twilight-toolkit/ui'
import { CardTool } from '~/components/Card/WithImage/tool'
import Aside from '~/components/Aside'
import PageView from '~/components/Helpers/pageView'
import getApi from '~/utilities/api'

export interface PostProps {
	params: {
		pid: string
	}
}

/**
 * Get post data for the blog post with id `pid`
 *
 * @param {string} pid
 * @return {*}  {Promise<WPPost>}
 */
export const getPostData = async (pid: string): Promise<WPPost> => {
	const res = await fetch(
		getApi({
			post: parseInt(pid),
		}),
		{
			next: {
				revalidate: 3600 * 24,
			},
		}
	)
	const data = await res.json()
	return data
}

/**
 * Get all the post ids
 *
 * @return {*}  {Promise<number[]>}
 */
const getPostIDs = async (): Promise<number[]> => {
	const res = await fetch(
		getApi({
			postIDs: true,
		})
	)
	const data = await res.json()
	return data
}

export const generateStaticParams = async (): Promise<{ pid: string }[]> => {
	const postIDs: number[] = await getPostIDs()
	return postIDs.map((id) => ({
		pid: id.toString(),
	}))
}

const BlogPost = async ({ params }: PostProps) => {
	const { pid } = params
	const post = await getPostData(pid)

	if (!post) {
		return redirect('/404')
	}

	return (
		<div>
			<PageView id={pid} />
			<article
				data-cy="postContent"
				className="lg:shadow-sm lg:border lg:rounded-xl bg-white dark:bg-gray-800 dark:border-gray-800 p-5 lg:p-20 lg:pt-20 pt-24"
			>
				<div className="mb-20">
					<div className="flex mb-3">
						<Link href={`/category/${post.post_categories[0].term_id}`}>
							<Label type="primary" icon="cate">
								{post.post_categories[0].name}
							</Label>
						</Link>
					</div>
					<h1 className="text-1.5 lg:text-postTitle font-medium tracking-wider leading-snug">
						{post.title.rendered}
					</h1>
					<p className="flex text-5 lg:text-xl text-gray-500 space-x-2 mt-2 tracking-wide whitespace-nowrap">
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
			<div className="lg:mt-5 border-t border-gray-200 lg:border-none">
				<SubscriptionBox type="lg" />
			</div>
			{/* <CommentBox /> */}
		</div>
	)
}

export default BlogPost
