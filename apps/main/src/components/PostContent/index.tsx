import "highlight.js/styles/atom-one-dark.css"
import Highlight from "react-highlight"

const HighlightComponent = Highlight as any

export default function PostContent({ content }: { content: string }) {
	return (
		<div className="prose tracking-wide dark:prose-dark lg:prose-xl">
			<HighlightComponent innerHTML={true}>{content}</HighlightComponent>
		</div>
	)
}
