import React from 'react'
import { propTypes } from '@twilight-toolkit/utils'
import icons from './icons'

const IconsNames = propTypes.tuple(...Object.keys(icons))
export type IconNames = typeof IconsNames[number]

export interface IconProps {
	name: IconNames
}

const Icon: React.FC<React.PropsWithChildren<IconProps>> = ({
	name,
}: IconProps) => {
	return icons[name]
}

export default Icon
