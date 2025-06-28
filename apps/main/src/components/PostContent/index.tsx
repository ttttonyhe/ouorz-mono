import "highlight.js/styles/atom-one-dark.css"
import { useEffect, useState } from "react"
import Highlight from "react-highlight"

interface PostContentProps {
	content: string
	onRendered?: () => void
}

export default function PostContent({ content, onRendered }: PostContentProps) {
	const [mounted, setMounted] = useState(false)

	useEffect(() => {
		setMounted(true)
	}, [])

	useEffect(() => {
		// Call onRendered after the component mounts and Highlight is ready
		if (mounted && onRendered) {
			const timer = setTimeout(() => {
				onRendered()
			}, 100) // Small delay to ensure Highlight has rendered

			return () => clearTimeout(timer)
		}
	}, [onRendered, mounted])

	if (!mounted) {
		return (
			<div className="prose dark:prose-dark lg:prose-xl prose-ul:m-2 prose-ul:ps-5 prose-hr:border-gray-200 dark:prose-hr:border-gray-700 tracking-wide">
				<div>Loading...</div>
			</div>
		)
	}

	return (
		<div className="prose dark:prose-dark lg:prose-xl prose-ul:m-2 prose-ul:ps-5 prose-hr:border-gray-200 dark:prose-hr:border-gray-700 tracking-wide">
			<Highlight innerHTML={true}>{content}</Highlight>
		</div>
	)
}
