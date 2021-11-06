import React from 'react'
import Icons from '~/components/Icons'

interface Props {
	bType: string
	icon?: string
	className?: string
	children?: React.ReactNode
	[prop: string]: any
}

export default function Button(props: Props) {
	const { bType, icon, className, children, ...rest } = props
	switch (bType) {
		case 'default':
			return (
				<button
					aria-label="default"
					className={`w-full py-2 px-7 shadow-sm border border-gray-300 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:border-gray-700 hover:shadow-inner rounded-md cursor-pointer focus:outline-none justify-center items-center text-xl tracking-wider bg-white flex ${
						className ? className : ''
					}`}
					{...rest}
				>
					{icon && (
						<span className={children ? 'w-6 h-6 mr-1' : 'w-6 h-6'}>
							{Icons[icon]}
						</span>
					)}
					{children}
				</button>
			)
		case 'menu-default':
			return (
				<button
					aria-label="menu-default"
					className={`w-max py-2 px-5 hover:bg-menu dark:hover:bg-gray-800 rounded-md cursor-pointer focus:outline-none justify-center items-center text-xl tracking-wider flex text-gray-500 dark:text-gray-400 ${
						className ? className : ''
					}`}
					{...rest}
				>
					{icon && (
						<span className={children ? 'w-6 h-6 mr-1' : 'w-6 h-6'}>
							{Icons[icon]}
						</span>
					)}
					{children}
				</button>
			)
		case 'primary':
			return (
				<button
					aria-label="primary"
					className={`w-full py-2 px-7 shadow-sm border border-blue-500 dark:border-blue-900 dark:bg-blue-900 dark:text-gray-300  bg-blue-500 hover:bg-blue-600 hover:border-blue-600 dark:hover:bg-blue-800 dark:hover:border-blue-800 hover:shadow-inner text-white rounded-md cursor-pointer focus:outline-none justify-center items-center text-xl tracking-wider flex ${
						className ? className : ''
					}`}
					{...rest}
				>
					{icon && (
						<span className={children ? 'w-6 h-6 mr-1' : 'w-6 h-6'}>
							{Icons[icon]}
						</span>
					)}
					{children}
				</button>
			)
		case 'menu-primary':
			return (
				<button
					aria-label="menu-primary"
					className={`w-max py-2 px-5 hover:bg-pink-100 dark:hover:bg-pink-900 rounded-md cursor-pointer focus:outline-none justify-center items-center text-xl tracking-wider flex text-pink-500 dark:text-pink-400 ${
						className ? className : ''
					}`}
					{...rest}
				>
					{icon && (
						<span className={children ? 'w-6 h-6 mr-1' : 'w-6 h-6'}>
							{Icons[icon]}
						</span>
					)}
					{children}
				</button>
			)
	}
}
