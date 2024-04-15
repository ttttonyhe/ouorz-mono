import type { FC, PropsWithChildren } from "react"

const BaseLayout: FC<PropsWithChildren> = ({ children }) => {
	return (
		<div className="relative z-main -mt-header h-screen w-full overflow-hidden overflow-y-auto pt-header">
			{children}
		</div>
	)
}

export default BaseLayout
