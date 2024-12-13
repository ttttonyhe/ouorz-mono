import { FC, PropsWithChildren } from "react"

const Callout: FC<PropsWithChildren> = ({ children }) => {
	return (
		<div className="border-l-4 border-yellow-500 bg-yellow-300 p-4">
			{children}
		</div>
	)
}

export default Callout
