import "highlight.js/styles/atom-one-dark.css"
import { useEffect, useRef } from "react"

interface PostContentProps {
	content: string
}

export default function PostContent({ content }: PostContentProps) {
	const contentRef = useRef<HTMLDivElement>(null)

	useEffect(() => {
		if (!content) return

		const codeBlocks = contentRef.current?.querySelectorAll<HTMLElement>(
			"pre code:not([data-highlighted])"
		)
		if (!codeBlocks?.length) return

		let cancelled = false
		void import("highlight.js")
			.then(({ default: highlighter }) =>
				cancelled
					? undefined
					: codeBlocks.forEach((code) => highlighter.highlightElement(code))
			)
			.catch((error: Error) => {
				console.error("Failed to load syntax highlighting", error)
			})

		return () => {
			cancelled = true
		}
	}, [content])

	return (
		<div className="prose tracking-wide lg:prose-xl dark:prose-dark prose-ul:m-2 prose-ul:ps-5 prose-hr:border-gray-200 dark:prose-hr:border-gray-700">
			<div ref={contentRef} dangerouslySetInnerHTML={{ __html: content }} />
		</div>
	)
}
