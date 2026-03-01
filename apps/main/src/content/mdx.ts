import type { MDXRemoteSerializeResult } from "next-mdx-remote"
import { serialize } from "next-mdx-remote/serialize"
import rehypeHighlight from "rehype-highlight"
import rehypeKatex from "rehype-katex"
import remarkGfm from "remark-gfm"
import remarkMath from "remark-math"

export const serializeMDX = async (
	raw: string
): Promise<MDXRemoteSerializeResult> => {
	return serialize(raw, {
		mdxOptions: {
			remarkPlugins: [remarkGfm, remarkMath],
			rehypePlugins: [rehypeKatex, rehypeHighlight],
		},
	})
}
