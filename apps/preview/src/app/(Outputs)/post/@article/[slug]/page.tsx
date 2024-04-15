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
		<article className="flex shrink-0 justify-center">
			<div
				className={cn(
					article.renderer,
					"mx-5 h-fit w-article-content min-w-0 pb-24 pt-14",
					// Prose
					"prose tracking-wide dark:prose-invert",
					// Links
					"prose-a:text-blue-600",
					// h1
					"prose-h1:text-3xl",
					// Code blocks
					[
						"prose-pre:rounded-lg",
						"prose-pre:border prose-pre:border-gray-200 prose-pre:bg-gray-100",
						"prose-pre:dark:border-neutral-700 prose-pre:dark:bg-neutral-800",
					],
					// Images
					"prose-img:rounded-lg"
				)}>
				<h1>{meta.title}</h1>
				<PostRenderer content={source} />
			</div>
		</article>
	)
}

export const generateStaticParams = () => {
	const posts = getPosts()
	return posts.map((post) => ({
		slug: post.slug,
	}))
}

// Let Next.js know that these pages needs to be statically generated, hence
// we don't need a SSR and Streaming fallback route
export const dynamicParams = false

export default Post
