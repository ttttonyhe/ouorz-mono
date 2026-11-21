"use client"

import cn from "clsx"
import { type FC, type PropsWithChildren, useEffect, useState } from "react"

const PostLayout: FC<PropsWithChildren> = ({ children }) => {
	// Enable scrolling after the aside slide-in animation finishes (300ms).
	const [ready, setReady] = useState(false)

	useEffect(() => {
		const timer = setTimeout(() => {
			setReady(true)
		}, 300)
		return () => clearTimeout(timer)
	}, [])

	return (
		<section
			className={cn(
				ready && "overflow-y-auto",
				"flex h-full w-full flex-col overflow-hidden"
			)}>
			<header className="sticky top-0 z-header flex h-header w-full shrink-0 items-center justify-center" />
			{children}
		</section>
	)
}

export default PostLayout
