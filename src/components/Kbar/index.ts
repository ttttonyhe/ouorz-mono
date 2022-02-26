import Kbar from './component'

export interface KbarListItem {
	id: string
	label: string
	color?: string
	bgColor?: string
	bgDark?: string
	icon?: string
	link?: {
		internal?: string
		external?: string
	}
	shortcut?: string[]
	action?: () => void
	description?: string
}

export interface KbarProps {
	list: KbarListItem[]
	keyBinding?: string[]
	placeholder?: string
	inputValue?: string
}

export default Kbar
