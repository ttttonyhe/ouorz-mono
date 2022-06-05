import React from 'react'
import icons from './icons'
import { IconNames } from '../utils/propTypes'

export interface IconProps {
	name: IconNames
}

const Icon: React.FC<React.PropsWithChildren<IconProps>> = ({
	name = 'empty',
}: IconProps) => {
	return icons[name]
}

export default Icon
