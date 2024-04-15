"use client"

import cn from "clsx"
import { useEffect, useState, type FC, type PropsWithChildren } from "react"

const ArticleLayout: FC<PropsWithChildren> = ({ children }) => {
	// Enable overflow-y-auto after Aside slide-in animation has completed (300ms)
	const [ready, setReady] = useState(false)

	useEffect(() => {
		setTimeout(() => {
			setReady(true)
		}, 300)
	}, [])

	return (
		<section
			className={cn(
				// Animation
				ready && "overflow-y-auto",
				// Prose
				"w-article md:w-article-md xl:w-article-xl prose max-w-full shrink-0 grow-0 overflow-hidden pb-24 dark:prose-invert dark:bg-neutral-900",
				// Links
				"prose-a:text-blue-600",
				// Paragraphs
				"prose-p:flex prose-p:items-center",
				// Code blocks
				[
					"prose-pre:rounded-lg",
					"prose-pre:border prose-pre:border-gray-200 prose-pre:bg-gray-100",
					"prose-pre:dark:border-neutral-700 prose-pre:dark:bg-neutral-800",
				],
				// Images
				"prose-img:rounded-lg"
			)}>
			{children}
		</section>
	)
}

export default ArticleLayout
