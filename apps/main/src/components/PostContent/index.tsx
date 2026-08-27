import "highlight.js/styles/atom-one-dark.css"
import Highlight from "react-highlight"

import useMounted from "~/hooks/useMounted"

interface PostContentProps {
	content: string
}

export default function PostContent({ content }: PostContentProps) {
	const mounted = useMounted()

	if (!mounted) {
		return (
			<div className="prose tracking-wide lg:prose-xl dark:prose-dark prose-ul:m-2 prose-ul:ps-5 prose-hr:border-gray-200 dark:prose-hr:border-gray-700">
				<div>Loading...</div>
			</div>
		)
	}

	return (
		<div className="prose tracking-wide lg:prose-xl dark:prose-dark prose-ul:m-2 prose-ul:ps-5 prose-hr:border-gray-200 dark:prose-hr:border-gray-700">
			<Highlight innerHTML={true}>{content}</Highlight>
		</div>
	)
}
