"use client"

import article from "@/styles/article.module.css"
import cn from "clsx"
import { useTheme } from "next-themes"
import type { FC, PropsWithChildren } from "react"
import { highlight } from "sugar-high"

const CodeBlock: FC<PropsWithChildren> = ({ children }) => {
	const { theme } = useTheme()
	const codeHtml = highlight(children as string)

	return (
		<code
			className={cn(theme === "dark" ? article.code_dark : article.code)}
			dangerouslySetInnerHTML={{ __html: codeHtml }}
		/>
	)
}

export default CodeBlock
