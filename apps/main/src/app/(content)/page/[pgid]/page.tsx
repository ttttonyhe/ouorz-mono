import { redirect } from 'next/navigation'
import TimeAgo from '~/components/TimeAgo'
import PageView from '~/components/Helpers/pageView'
import getApi from '~/utilities/api'
import SubscriptionBox from '~/components/SubscriptionBox'
import CommentBox from '~/components/CommentBox'
import PostContent from '~/components/PostContent'

export interface PageProps {
	params: {
		pgid: string
	}
}

/**
 * Get page data for the blog page with id `pgid`
 *
 * @param {string} pgid
 * @return {*}  {Promise<WPPost>}
 */
export const getPageData = async (pgid: string): Promise<WPPost> => {
	const res = await fetch(
		getApi({
			page: parseInt(pgid),
		})
	)
	const data = await res.json()
	return data
}

const BlogPage = async ({ params }: PageProps) => {
	const { pgid } = params
	const page = await getPageData(pgid)

	if (!page) {
		return redirect('/404')
	}

	return (
		<>
			<PageView id={pgid} />
			<article
				data-cy="pageContent"
				className="lg:shadow-sm lg:border lg:rounded-xl bg-white dark:bg-gray-800 dark:border-gray-800 p-5 lg:p-20 pt-24"
			>
				<div className="mb-20">
					<h1 className="text-1.5 lg:text-postTitle font-medium tracking-wider leading-snug">
						{page.title.rendered}
					</h1>
					<p className="flex text-5 lg:text-xl text-gray-500 dark:text-gray-400 space-x-2 mt-2 tracking-wide">
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
		</>
	)
}

export default BlogPage
