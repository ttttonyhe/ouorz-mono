import type { FC, PropsWithChildren } from "react"

interface SidebarSectionProps {
	title?: string
}

const SidebarSection: FC<PropsWithChildren<SidebarSectionProps>> = ({
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

export default SidebarSection
