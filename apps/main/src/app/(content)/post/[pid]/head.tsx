import { getPostData, PostProps } from './page'
import trimStr from '~/utilities/trimString'

const PostHead = async ({ params }: PostProps) => {
	const post = await getPostData(params.pid)
	const title = `${post.title.rendered} - TonyHe`

	return (
		<>
			<title>{title}</title>
			<link
				rel="icon"
				href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📃</text></svg>"
			/>
			<meta name="description" content={trimStr(post.post_excerpt.four, 150)} />
			{post.post_img.url && (
				<meta property="og:image" content={post.post_img.url} />
			)}
		</>
	)
}

export default PostHead
