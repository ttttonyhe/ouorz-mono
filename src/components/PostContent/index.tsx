import Highlight from 'react-highlight'
import 'highlight.js/styles/atom-one-dark.css'

export default function PostContent({ content }: { content: string }) {
	return (
		<div className="prose lg:prose-xl tracking-wide dark:prose-dark">
			<Highlight innerHTML={true}>{content}</Highlight>
		</div>
	)
}
