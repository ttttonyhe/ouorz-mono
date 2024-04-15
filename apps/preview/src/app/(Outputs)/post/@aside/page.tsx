"use client"

import responsive from "@/styles/responsive.module.css"
import cn from "clsx"
import { useState } from "react"

const PostAside = () => {
	const [count, setCount] = useState(0)

	return (
		<section
			className={cn(
				responsive["aside-width"],
				"animate-aside-slide-in shrink-0 grow-0 overflow-hidden overflow-y-auto border-r dark:border-neutral-800 dark:bg-neutral-900"
			)}>
			<p>Aside {count}</p>
			<button onClick={() => setCount(count + 1)}>Increment</button>
		</section>
	)
}

export default PostAside
