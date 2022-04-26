import React from 'react'
import { IconNames } from '~/constants/propTypes'
import icons from './icons'

export interface IconProps {
	name: IconNames
}

const Icon: React.FC<React.PropsWithChildren<IconProps>> = ({
	name,
}: IconProps) => {
	return icons[name]
}

export default Icon
