import components from "@/components/MDX/components"
import { evaluate } from "@mdx-js/mdx"
import { Suspense, type FC } from "react"
import * as runtime from "react/jsx-runtime"
import rehypeAutolinkHeadings from "rehype-autolink-headings"
import rehypeMathjax from "rehype-mathjax"
import rehypeSlug from "rehype-slug"
import remarkMath from "remark-math"

interface MDXPostRendererProps {
	content: string
}

const RenderedContent = async ({ content }: MDXPostRendererProps) => {
	const { default: Content } = await evaluate(content, {
		...runtime,
		development: false,
		remarkPlugins: [remarkMath],
		rehypePlugins: [rehypeSlug, rehypeAutolinkHeadings, rehypeMathjax],
	})
	return <Content components={components} />
}

const MDXPostRenderer: FC<MDXPostRendererProps> = ({ content }) => {
	return (
		<Suspense fallback={<>Suspense Loading...</>}>
			<RenderedContent content={content} />
		</Suspense>
	)
}

export default MDXPostRenderer
