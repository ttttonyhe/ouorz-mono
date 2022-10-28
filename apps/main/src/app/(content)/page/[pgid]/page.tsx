import getApi from '~/utilities/api'
import PostContent from '~/components/PostContent'

export interface BlogPageProps {
	params: {
		pgid: number
	}
}

/**
 * Get page data from WP REST API
 *
 * @param {number} pageID
 * @return {*}  {Promise<WPPost>}
 */
export const getBlogPageData = async (pageID: number): Promise<WPPost> => {
	const res = await fetch(
		getApi({
			page: pageID,
		})
	)
	const data = await res.json()
	return data
}

const BlogPage = async ({ params }: BlogPageProps) => {
	const page = await getBlogPageData(params.pgid)

	return <PostContent content={page.content.rendered} />
}

export default BlogPage
