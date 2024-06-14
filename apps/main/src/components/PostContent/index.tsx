import "highlight.js/styles/atom-one-dark.css"
import Highlight from "react-highlight"

const HighlightComponent = Highlight as any

export default function PostContent({ content }: { content: string }) {
	return (
		<div className="prose tracking-wide dark:prose-dark lg:prose-xl prose-ul:m-2 prose-ul:ps-5 prose-hr:border-gray-200 prose-hr:dark:border-gray-700">
			<HighlightComponent innerHTML={true}>{content}</HighlightComponent>
		</div>
	)
}
