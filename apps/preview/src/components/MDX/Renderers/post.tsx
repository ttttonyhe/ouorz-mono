import components from "@/components/MDX"
import { MDXRemote } from "next-mdx-remote/rsc"
import type { FC } from "react"
import rehypeMathjax from "rehype-mathjax"
import remarkGfm from "remark-gfm"
import remarkMath from "remark-math"

interface PostRendererProps {
	content: string
}

const PostRenderer: FC<PostRendererProps> = ({ content }) => {
	return (
		<MDXRemote
			source={content}
			components={components}
			options={{
				mdxOptions: {
					remarkPlugins: [remarkGfm, remarkMath],
					rehypePlugins: [rehypeMathjax],
				},
				parseFrontmatter: false,
			}}
		/>
	)
}

export default PostRenderer
