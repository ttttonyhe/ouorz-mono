import { FC, PropsWithChildren } from "react"

const ViewLayout: FC<PropsWithChildren> = ({ children }) => {
	return <section className="relative w-full">{children}</section>
}

export default ViewLayout
