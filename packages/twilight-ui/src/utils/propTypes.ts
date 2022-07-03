import { propTypes } from '@twilight-toolkit/utils'
import icons from '../Icon/icons'

export const buttonTypes = propTypes.tuple(
	'default',
	'menu-default',
	'primary',
	'menu-primary'
)
export const labelTypes = propTypes.tuple(
	'sticky',
	'primary',
	'secondary',
	'green',
	'gray'
)
export const iconsNames = propTypes.tuple(...Object.keys(icons))

export type ButtonTypes = typeof buttonTypes[number]
export type IconNames = typeof iconsNames[number]
export type LabelTypes = typeof labelTypes[number]
