"use client"

import cn from "clsx"
import { useTheme } from "next-themes"
import type { FC } from "react"
import { highlight } from "sugar-high"

import article from "@/styles/article.module.css"

interface CodeBlockProps {
	/** MDX passes the raw source of a fenced block as a string. */
	children?: string
}

const CodeBlock: FC<CodeBlockProps> = ({ children }) => {
	const { theme } = useTheme()
	const codeHtml = highlight(children ?? "")

	return (
		<code
			className={cn(theme === "dark" ? article.code_dark : article.code)}
			dangerouslySetInnerHTML={{ __html: codeHtml }}
		/>
	)
}

export default CodeBlock
