import Highlight from "react-highlight"
import "highlight.js/styles/atom-one-dark.css"

const HighlightComponent = Highlight as any

export default function PostContent({ content }: { content: string }) {
	return (
		<div className="prose lg:prose-xl tracking-wide dark:prose-dark">
			<HighlightComponent innerHTML={true}>{content}</HighlightComponent>
		</div>
	)
}
