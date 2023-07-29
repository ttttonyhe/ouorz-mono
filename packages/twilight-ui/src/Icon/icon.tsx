import React from "react"
import icons from "./icons"
import { IconNames } from "../utils/propTypes"

export interface IconProps {
	/**
	 * Specify the name of the icon
	 */
	name: IconNames
}

export const Icon = ({ name = "empty" }: IconProps) => {
	return icons[name]
}

export default Icon
