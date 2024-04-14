import type { FC, PropsWithChildren } from "react"

const BaseLayout: FC<PropsWithChildren> = ({ children }) => {
	return (
		<article className="relative z-main h-full w-full p-4">
			{children}
		</article>
	)
}

export default BaseLayout
