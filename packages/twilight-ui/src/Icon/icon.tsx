import { IconNames } from "../utils/propTypes"
import icons from "./icons"

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
