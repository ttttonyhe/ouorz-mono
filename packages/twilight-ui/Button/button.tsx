import React from 'react'
import Icon from '../Icon'
import type { ButtonTypes, IconNames } from '../utils/propTypes'

interface Props {
	type: ButtonTypes
	icon?: IconNames
	className?: string
	children?: React.ReactNode
	[prop: string]: any
}
type NativeAttrs = Omit<React.ButtonHTMLAttributes<any>, keyof Props>
export type ButtonProps = Props & NativeAttrs

const Button = ({
	type = 'default',
	icon,
	className,
	children = 'Button',
	...rest
}: ButtonProps) => {
	switch (type) {
		case 'menu-default':
			return (
				<button
					aria-label="menu-default"
					className={`${
						className || ''
					} w-max py-2 px-5 hover:bg-menu dark:hover:bg-gray-800 rounded-md cursor-pointer focus:outline-none justify-center items-center text-xl tracking-wider flex text-gray-500 dark:text-gray-400`}
					{...rest}
				>
					{icon && (
						<span className={children ? 'w-6 h-6 mr-1' : 'w-6 h-6'}>
							<Icon name={icon} />
						</span>
					)}
					{children}
				</button>
			)
		case 'primary':
			return (
				<button
					aria-label="primary"
					className={`${
						className || ''
					} w-max py-2 px-7 shadow-sm border border-blue-500 dark:border-blue-900 dark:bg-blue-900 dark:text-gray-300 bg-blue-500 hover:bg-blue-600 hover:border-blue-600 dark:hover:bg-blue-800 dark:hover:border-blue-800 hover:shadow-inner text-white rounded-md cursor-pointer focus:outline-none justify-center items-center text-xl tracking-wider flex`}
					{...rest}
				>
					{icon && (
						<span className={children ? 'w-6 h-6 mr-1' : 'w-6 h-6'}>
							<Icon name={icon} />
						</span>
					)}
					{children}
				</button>
			)
		case 'menu-primary':
			return (
				<button
					aria-label="menu-primary"
					className={`${
						className || ''
					} w-max py-2 px-5 hover:bg-pink-100 dark:hover:bg-pink-900 rounded-md cursor-pointer focus:outline-none justify-center items-center text-xl tracking-wider flex text-pink-500 dark:text-pink-400`}
					{...rest}
				>
					{icon && (
						<span className={children ? 'w-6 h-6 mr-1' : 'w-6 h-6'}>
							<Icon name={icon} />
						</span>
					)}
					{children}
				</button>
			)
		default:
			return (
				<button
					aria-label="default"
					className={`${
						className || ''
					} w-max py-2 px-7 shadow-sm border border-gray-300 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:border-gray-700 hover:shadow-inner rounded-md cursor-pointer focus:outline-none justify-center items-center text-xl tracking-wider bg-white flex`}
					{...rest}
				>
					{icon && (
						<span className={children ? 'w-6 h-6 mr-1' : 'w-6 h-6'}>
							<Icon name={icon} />
						</span>
					)}
					{children}
				</button>
			)
	}
}

export default Button
