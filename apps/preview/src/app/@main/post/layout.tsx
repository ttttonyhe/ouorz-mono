"use client"

import cn from "clsx"
import { PropsWithChildren, useEffect, useState, type FC } from "react"

const PostLayout: FC<PropsWithChildren> = ({ children }) => {
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
				ready && "overflow-y-auto",
				"flex h-full w-full flex-col overflow-hidden"
			)}>
			<header className="sticky top-0 z-header flex h-header w-full shrink-0 items-center justify-center" />
			{children}
		</section>
	)
}

export default PostLayout
