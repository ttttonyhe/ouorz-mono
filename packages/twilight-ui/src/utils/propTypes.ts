import { propTypes } from '@twilight-toolkit/utils'
import icons from '../Icon/icons'

const buttonTypes = propTypes.tuple(
	'default',
	'menu-default',
	'primary',
	'menu-primary'
)
const IconsNames = propTypes.tuple(...Object.keys(icons))

export type ButtonTypes = typeof buttonTypes[number]
export type IconNames = typeof IconsNames[number]
