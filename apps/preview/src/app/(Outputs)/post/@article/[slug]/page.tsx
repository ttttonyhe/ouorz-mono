import PostRenderer from "@/components/MDX/Renderers"
import { getPosts, getPostBySlug } from "@/database/getContent"
import article from "@/styles/article.module.css"
import cn from "clsx"
import { FC } from "react"

export interface PostProps {
	params: {
		slug: string
	}
}

const Post: FC<PostProps> = ({ params: { slug } }) => {
	const {
		data: { meta, source },
	} = getPostBySlug(slug)

	return (
		<article
			className={cn(
				article.renderer,
				"mx-5 h-fit w-article-content min-w-0 pb-24 pt-14"
			)}>
			<h1>{meta.title}</h1>
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

export const runtime = 'edge'

export default Post
