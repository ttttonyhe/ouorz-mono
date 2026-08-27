import { FC, PropsWithChildren } from "react"

interface CustomALinkProps {
	href?: string
}

const CustomALink: FC<PropsWithChildren<CustomALinkProps>> = ({
	children,
	href,
}) => {
	if (!href) return null

	if (href.startsWith("#")) {
		return <a href={href}>{children}</a>
	}

	return (
		<a target="_blank" rel="noopener noreferrer" href={href}>
			{children}
		</a>
	)
}

export default CustomALink
