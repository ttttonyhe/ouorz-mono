import { tuple } from '~/utilities/dataTypes'
import icons from '~/components/Icon/icons'

const listTypes = tuple('index', 'cate', 'search')
const labelTypes = tuple('sticky', 'primary', 'secondary', 'green', 'gray')
const buttonTypes = tuple('default', 'menu-default', 'primary', 'menu-primary')
const IconsNames = tuple(...Object.keys(icons))

export type ListTypes = typeof listTypes[number]
export type LabelTypes = typeof labelTypes[number]
export type IconNames = typeof IconsNames[number]
export type ButtonTypes = typeof buttonTypes[number]
