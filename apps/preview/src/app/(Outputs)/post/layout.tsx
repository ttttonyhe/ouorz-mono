"use client"

import responsive from "@/styles/responsive.module.css"
import cn from "clsx"
import { useEffect, useState, type FC, type ReactNode } from "react"

interface PostLayoutProps {
	article: ReactNode
	aside: ReactNode
}

const PostLayout: FC<PostLayoutProps> = ({ article, aside }) => {
	// Enable overflow-y-auto after Aside slide-in animation has completed (300ms)
	const [ready, setReady] = useState(false)

	useEffect(() => {
		setTimeout(() => {
			setReady(true)
		}, 300)
	}, [])

	return (
		<section className="flex h-full w-full">
			<section
				className={cn(
					responsive["aside-width"],
					"flex h-full animate-aside-slide-in flex-col bg-white-tinted"
				)}>
				<header className="sticky top-0 flex h-header w-full shrink-0 items-center border-b border-r dark:border-neutral-800 dark:bg-neutral-900">
					<h1>Aside</h1>
				</header>
				{aside}
			</section>
			<section
				className={cn(
					ready && "overflow-y-auto",
					"flex w-article flex-col overflow-hidden md:w-article-md xl:w-article-xl"
				)}>
				<header className="sticky top-0 z-header flex h-header w-full shrink-0 items-center justify-center" />
				{article}
			</section>
		</section>
	)
}

export default PostLayout
