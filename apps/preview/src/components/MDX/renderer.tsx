import { evaluate } from "@mdx-js/mdx"
import { Suspense, type FC } from "react"
import * as runtime from "react/jsx-runtime"
import rehypeMathjax from "rehype-mathjax"
import remarkMath from "remark-math"

import components from "@/components/MDX/components"

interface MDXPostRendererProps {
	content: string
}

const RenderedContent = async ({ content }: MDXPostRendererProps) => {
	const { default: Content } = await evaluate(content, {
		...runtime,
		development: false,
		remarkPlugins: [remarkMath],
		rehypePlugins: [rehypeMathjax],
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
