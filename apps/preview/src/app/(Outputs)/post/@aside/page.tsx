"use client"

import { useState } from "react"

const PostAside = () => {
	const [count, setCount] = useState(0)

	return (
		<nav className="z-aside h-full overflow-hidden overflow-y-auto border-r bg-white-tinted dark:border-neutral-800 dark:bg-neutral-900">
			<p>{count}</p>
			<button onClick={() => setCount(count + 1)}>Increment</button>
		</nav>
	)
}

export default PostAside
