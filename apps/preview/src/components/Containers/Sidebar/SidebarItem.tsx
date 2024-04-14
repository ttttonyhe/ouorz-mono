import SidebarContext from "./context"
import cn from "clsx"
import Link from "next/link"
import { useContext, type FC, type PropsWithChildren } from "react"

interface SidebarItemProps {
	pathname: string
}

const SidebarItem: FC<PropsWithChildren<SidebarItemProps>> = ({
	children,
	pathname,
}) => {
	const { activePathname } = useContext(SidebarContext)
	return (
		<li>
			<Link
				href={pathname}
				className={cn(activePathname === pathname && "bg-green-500")}>
				{children}
			</Link>
		</li>
	)
}

export default SidebarItem
