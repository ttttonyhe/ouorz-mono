import type { FC, PropsWithChildren } from "react"

interface MenuSectionProps {
	title?: string
}

const MenuSection: FC<PropsWithChildren<MenuSectionProps>> = ({
	title,
	children,
}) => {
	return (
		<section>
			{title && <h1>{title}</h1>}
			<ul>{children}</ul>
		</section>
	)
}

export default MenuSection
