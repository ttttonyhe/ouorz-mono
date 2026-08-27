import type { MDXComponents } from "mdx/types"

import { customMDXComponents } from "@/components/MDX"

export function useMDXComponents(components: MDXComponents): MDXComponents {
	return {
		...customMDXComponents,
		...components,
	}
}
