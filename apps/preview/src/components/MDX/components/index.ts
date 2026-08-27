import { MDXComponents } from "mdx/types"

import CustomALink from "./ALink"
import Callout from "./Callout"
import CodeBlock from "./CodeBlock"
import CustomLink from "./Link"

const components: MDXComponents = {
	Callout,
	code: CodeBlock,
	a: CustomALink,
	Link: CustomLink,
}

export default components
