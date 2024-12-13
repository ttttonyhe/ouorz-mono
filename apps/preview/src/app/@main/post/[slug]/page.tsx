import { MDXPostRenderer } from "@/components/MDX"
import { getPostBySlug, getPostSlugs } from "@/database/getContent"
import article from "@/styles/article.module.css"
import cn from "clsx"
import { FC } from "react"

export interface PostProps {
	params: Promise<{
		slug: string
	}>
}

const getPost = (slug: string) => {
	const { data } = getPostBySlug(slug)
	return data
}

const PostPage: FC<PostProps> = async ({ params }) => {
	const { slug } = await params
	const { meta, source } = getPost(slug)

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
				<MDXPostRenderer content={source} />
			</div>
		</article>
	)
}

// Generate static paths for all posts (SSG)
export const generateStaticParams = () => {
	return getPostSlugs().map((slug) => ({
		slug,
	}))
}

// Posts that are not generated at build time will return 404
export const dynamicParams = false

export default PostPage
