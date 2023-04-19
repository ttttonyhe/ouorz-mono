import React from "react"
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
	onClick?: () => void
}

export interface TabsProps {
	items: TabItemProps[]
	direction?: "vertical"
	defaultHighlighted?: boolean
	verticalListWrapper?: React.MutableRefObject<HTMLElement>
}

export interface TabItemComponentProps extends TabItemProps {
	index: number
}

export default Tabs
