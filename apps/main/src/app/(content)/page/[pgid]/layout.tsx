import { notFound } from 'next/navigation'
import TimeAgo from 'react-timeago'
import getApi from '~/utilities/api'
import SubscriptionBox from '~/components/SubscriptionBox'
import CommentBox from '~/components/CommentBox'
import { BlogPageProps, getBlogPageData } from './page'

interface BlogPageLayoutProps extends BlogPageProps {
	children: React.ReactNode
}

/**
 * Record a page view through WP REST API
 *
 * @param {number} pageID page id
 */
const recordPageView = async (pageID: number): Promise<void> => {
	await fetch(
		getApi({
			visit: pageID,
		}),
		{
			cache: 'no-cache',
		}
	)
}

const BlogPage = async ({ params, children }: BlogPageLayoutProps) => {
	const page = await getBlogPageData(params.pgid)

	if (!page) {
		return notFound()
	}

	await recordPageView(params.pgid)

	return (
		<>
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
				{children}
			</article>
			<div className="mt-5">
				<SubscriptionBox type="lg" />
			</div>
			{/* TODO: check if pgid needs to pass in to commentbox component */}
			<CommentBox />
		</>
	)
}

export default BlogPage
