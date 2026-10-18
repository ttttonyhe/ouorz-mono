import { evaluate } from "@mdx-js/mdx"
import { createElement } from "react"
import { renderToStaticMarkup } from "react-dom/server"
import * as runtime from "react/jsx-runtime"
import rehypeHighlight from "rehype-highlight"
import rehypeKatex from "rehype-katex"
import remarkGfm from "remark-gfm"
import remarkMath from "remark-math"

export const renderMDX = async (raw: string): Promise<string> => {
	// Only repository content reaches this evaluator; requests cannot supply MDX.
	const { default: Content } = await evaluate(raw, {
		...runtime,
		development: false,
		remarkPlugins: [remarkGfm, remarkMath],
		rehypePlugins: [rehypeKatex, rehypeHighlight],
	})
	return renderToStaticMarkup(createElement(Content))
}
