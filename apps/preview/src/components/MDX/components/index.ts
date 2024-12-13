import CustomALink from "./ALink"
import Callout from "./Callout"
import CodeBlock from "./CodeBlock"
import CustomLink from "./Link"
import { MDXComponents } from "mdx/types"

export default {
	Callout,
	code: CodeBlock,
	a: CustomALink,
	Link: CustomLink,
} as MDXComponents
