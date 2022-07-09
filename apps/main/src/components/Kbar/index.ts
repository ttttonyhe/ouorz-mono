import Kbar from './component'

export type KbarListItem = {
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
	hoverable?: boolean
	singleton?: boolean
	sublist?: {
		key: string
		list: KbarListItem[]
		placeholder?: string
	}
}

export interface KbarProps {
	list: KbarListItem[]
	keyBinding?: string[]
}

export default Kbar
