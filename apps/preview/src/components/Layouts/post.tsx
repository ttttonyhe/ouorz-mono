import post from "@/styles/post.module.css"
import cn from "clsx"
import type { FC, PropsWithChildren } from "react"
import { Suspense } from "react"

const PostLayout: FC<PropsWithChildren> = ({ children }) => {
	return (
		<Suspense fallback={<>Loading...</>}>
			<div className={cn(post.layout, "prose")}>{children}</div>
		</Suspense>
	)
}

export default PostLayout
