import globalComponents from "@/components/MDX"
import type { MDXComponents } from "mdx/types"

export function useMDXComponents(components: MDXComponents): MDXComponents {
	return {
		...globalComponents,
		...components,
	}
}
