import type { IconNames } from "../utils/propTypes"
import icons from "./icons"

export interface IconProps {
	name: IconNames
}

export const Icon = ({ name = "empty" }: IconProps) => icons[name]

export default Icon
