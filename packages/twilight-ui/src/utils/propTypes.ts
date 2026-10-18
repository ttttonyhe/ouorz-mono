import { propTypes } from "@twilight-toolkit/utils"

import icons from "../Icon/icons"

export const buttonTypes = propTypes.tuple(
	"default",
	"menu-default",
	"primary",
	"menu-primary"
)
export const labelTypes = propTypes.tuple(
	"primary",
	"secondary",
	"green",
	"gray-icon",
	"orange-icon",
	"sticky-icon",
	"green-icon"
)
export type ButtonTypes = (typeof buttonTypes)[number]
export type IconNames = keyof typeof icons
export type LabelTypes = (typeof labelTypes)[number]

export const isIconName = (name: string): name is IconNames =>
	Object.hasOwn(icons, name)

export const iconsNames = Object.keys(icons).filter((name) => isIconName(name))
