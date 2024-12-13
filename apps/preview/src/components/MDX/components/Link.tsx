import Link from "next/link"
import { FC, PropsWithChildren } from "react"

interface CustomLinkProps {
	href?: string
}

const CustomLink: FC<PropsWithChildren<CustomLinkProps>> = ({
	children,
	href,
}) => {
	if (!href) return null

	return <Link href={href}>{children}</Link>
}

export default CustomLink
