import components from "@/components/MDX/components"
import { MDXRemote } from "next-mdx-remote/rsc"
import { Suspense, type FC } from "react"
// import rehypeAutolinkHeadings from "rehype-autolink-headings"
import rehypeMathjax from "rehype-mathjax"
// import rehypeSlug from "rehype-slug"
import remarkMath from "remark-math"

interface MDXPostRendererProps {
	content: string
}

const MDXPostRenderer: FC<MDXPostRendererProps> = ({ content }) => {
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

export default MDXPostRenderer
