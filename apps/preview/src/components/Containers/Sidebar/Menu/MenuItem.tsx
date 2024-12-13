import MenuContext from "../context"
import cn from "clsx"
import Link from "next/link"
import { useContext, type FC, type PropsWithChildren } from "react"

interface MenuItemProps {
	pathname: string
}

const MenuItem: FC<PropsWithChildren<MenuItemProps>> = ({
	children,
	pathname,
}) => {
	const { activePathname } = useContext(MenuContext)
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

export default MenuItem
