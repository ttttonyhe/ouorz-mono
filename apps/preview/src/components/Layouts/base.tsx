import type { FC, PropsWithChildren } from "react"

const BaseLayout: FC<PropsWithChildren> = ({ children }) => {
	return (
		<div className="-mt-header pt-header relative z-main h-screen w-full overflow-hidden overflow-y-auto">
			{children}
		</div>
	)
}

export default BaseLayout
