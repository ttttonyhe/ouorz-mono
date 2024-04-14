import type { FC, PropsWithChildren } from "react"

const CodeBlock: FC<PropsWithChildren> = ({ children }) => {
	return (
		<pre className="bg-gray-200">
			<code>{children}</code>
		</pre>
	)
}

export default CodeBlock
