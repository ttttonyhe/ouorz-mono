import type React from "react"

import Tabs from "./component"

export interface TabItemProps {
	className?: string
	label: string
	component?: React.ReactNode
	hoverable?: boolean
	color?: string
	bgColor?: string
	bgDark?: string
	icon?: string
	link?: {
		internal?: string
		external?: string
	}
	shortcut?: string[]
	description?: string
	onClick?: () => void
}

export interface TabsProps {
	items: TabItemProps[]
	direction?: "vertical"
	defaultHighlighted?: boolean
	verticalListWrapper?: React.RefObject<HTMLElement | null>
	/** Called with the height the vertical list wants its wrapper to have. */
	onListHeightChange?: (height: number) => void
}

export interface TabItemComponentProps extends TabItemProps {
	index: number
}

export default Tabs
