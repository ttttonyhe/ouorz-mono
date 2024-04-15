import components from "@/components/MDX"
import { MDXRemote } from "next-mdx-remote/rsc"
import { Suspense, type FC } from "react"
// import rehypeAutolinkHeadings from "rehype-autolink-headings"
import rehypeMathjax from "rehype-mathjax"
// import rehypeSlug from "rehype-slug"
import remarkMath from "remark-math"

interface PostServerRendererProps {
	content: string
}

const PostServerRenderer: FC<PostServerRendererProps> = ({ content }) => {
	return (
		<Suspense fallback={<>Suspense Loading...</>}>
			<MDXRemote
				source={content}
				components={components}
				options={{
					mdxOptions: {
						format: "mdx",
						remarkPlugins: [remarkMath],
						rehypePlugins: [rehypeMathjax],
					},
					parseFrontmatter: false,
				}}
			/>
		</Suspense>
	)
}

export default PostServerRenderer
