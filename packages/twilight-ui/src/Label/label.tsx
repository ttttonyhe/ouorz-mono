import React from 'react'
import Icon from '../Icon'
import type { LabelTypes, IconNames } from '../utils/propTypes'

interface Props {
	/**
	 * Specify the type of the label
	 */
	type: LabelTypes
	/**
	 * Specify the name of the icon to be used
	 */
	icon?: IconNames
	/**
	 * The content inside the button
	 */
	children?: React.ReactNode
	/**
	 * Preview style
	 */
	preview?: boolean
	/**
	 * Specify the class name of the icon
	 */
	iconClassName?: string
}

type NativeAttrs = Omit<React.LabelHTMLAttributes<any>, keyof Props>
export type LabelProps = Props & NativeAttrs

const Label: React.FC<LabelProps> = ({
	type,
	icon,
	children,
	preview,
	iconClassName,
	...props
}) => {
	switch (type) {
		case 'primary':
			return (
				<label
					{...props}
					className="cursor-pointer justify-center font-medium items-center flex w-auto lg:px-4 lg:py-1 px-2 py-1 bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 text-center rounded-md text-4 lg:text-label tracking-wide text-blue-500 dark:text-blue-300 align-middle effect-pressing"
				>
					{icon && (
						<span className="lg:w-7 lg:h-7 h-4 w-4 lg:mr-2 mr-1">
							<Icon name={icon} />
						</span>
					)}
					{children}
				</label>
			)
		case 'secondary':
			return (
				<label
					{...props}
					className="cursor-pointer focus:animate-pulse justify-center font-medium items-center flex w-auto lg:px-4 px-2 py-1 lg:py-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-center rounded-md text-4 lg:text-label tracking-wide text-gray-500 dark:text-gray-300 align-middle effect-pressing"
				>
					{icon && (
						<span className="lg:w-7 lg:h-7 h-4 w-4 lg:mr-2 mr-1">
							<Icon name={icon} />
						</span>
					)}
					{children}
				</label>
			)
		case 'green':
			return (
				<label
					className={`group cursor-pointer justify-center font-medium items-center h-full flex w-min ${
						preview ? 'px-3 py-0.5' : 'px-4 py-1.5'
					} bg-green-100 dark:bg-green-800 hover:bg-green-200 dark:hover:bg-green-700 text-center rounded-md text-xl tracking-wide text-green-500 dark:text-green-400 align-middle effect-pressing`}
				>
					{children}
					{icon && (
						<span className="w-5 h-5 opacity-0 transition-all ease-in-out -ml-5 group-hover:ml-1 group-hover:-mr-1 group-hover:opacity-100">
							<Icon name={icon} />
						</span>
					)}
				</label>
			)
		case 'sticky-icon':
			return (
				<label
					{...props}
					className="justify-center items-center flex w-auto h-auto lg:py-1 lg:px-3 py-0 px-2 bg-yellow-200 dark:bg-yellow-800 hover:bg-yellow-300 dark:hover:bg-yellow-700 text-center rounded-md text-4 lg:text-label tracking-wide text-yellow-500 align-middle"
				>
					<span className="lg:w-7 lg:h-7 h-4 w-4">
						<Icon name="sticky" />
					</span>
				</label>
			)
		case 'gray-icon':
			return (
				<label
					{...props}
					className="cursor-pointer justify-center font-medium items-center h-full flex w-min px-2 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-center rounded-md text-xl tracking-wide text-gray-500 dark:text-gray-300 align-middle effect-pressing"
				>
					{icon && (
						<span className="lg:w-[19px] lg:h-[19px] h-4 w-4">
							<Icon name={icon} />
						</span>
					)}
				</label>
			)
		case 'green-icon':
			return (
				<label
					{...props}
					className="cursor-pointer justify-center font-medium items-center h-full flex w-min px-2 py-2 bg-green-100 dark:bg-green-700 hover:bg-green-200 dark:hover:bg-green-600 text-center rounded-md text-xl tracking-wide text-green-500 dark:text-green-300 align-middle effect-pressing"
				>
					{icon && (
						<span className="lg:w-[19px] lg:h-[19px] h-4 w-4">
							<Icon name={icon} />
						</span>
					)}
				</label>
			)
		case 'orange-icon':
			return (
				<label
					{...props}
					className="cursor-pointer justify-center font-medium items-center h-full flex w-min px-2 py-2 bg-orange-100 dark:bg-orange-700 hover:bg-orange-200 dark:hover:bg-orange-600 text-center rounded-md text-xl tracking-wide text-orange-500 dark:text-orange-300 align-middle effect-pressing"
				>
					{icon && (
						<span
							className={`lg:w-[19px] lg:h-[19px] h-4 w-4 ${iconClassName}`}
						>
							<Icon name={icon} />
						</span>
					)}
				</label>
			)
	}
}

Label.defaultProps = {
	type: 'primary',
	icon: '',
	preview: false,
	children: 'Label',
	iconClassName: '',
}
Label.displayName = 'Label'
export default Label
