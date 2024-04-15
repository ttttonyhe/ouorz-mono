import PostRenderer from "@/components/MDX/Renderers"
import { getPosts, getPostBySlug } from "@/database/getContent"
import article from "@/styles/article.module.css"
import { FC } from "react"

export interface PostProps {
	params: {
		slug: string
	}
}

const Post: FC<PostProps> = ({ params: { slug } }) => {
	const {
		data: { source },
	} = getPostBySlug(slug)

	return (
		<article className={article.renderer}>
			<PostRenderer content={source} />
		</article>
	)
}

export const generateStaticParams = () => {
	const posts = getPosts()
	return posts.map((post) => ({
		slug: post.slug,
	}))
}

export default Post
