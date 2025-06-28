import Icon from "../Icon"
import type { ButtonTypes, IconNames } from "../utils/propTypes"
import React from "react"

interface Props {
	/**
	 * Specify the type of the button
	 */
	type?: ButtonTypes
	/**
	 * Specify the name of the icon to be used
	 */
	icon?: IconNames
	/**
	 * Custom classname
	 */
	className?: string
	/**
	 * The content inside the button
	 */
	children?: React.ReactNode
	[prop: string]: any
}

type NativeAttrs = Omit<React.ButtonHTMLAttributes<any>, keyof Props>
export type ButtonProps = Props & NativeAttrs

interface TemplateProps {
	defaultClassName?: string
	icon?: IconNames
	className?: string
	children?: React.ReactNode
}

const Template = ({
	children,
	className,
	defaultClassName,
	icon,
	...args
}: TemplateProps) => {
	return (
		<button
			type="button"
			className={`${
				className ? `${className} ` : ""
			}${defaultClassName} effect-pressing`}
			{...args}>
			{icon && (
				<span className={`h-6 w-6 shrink-0 ${children ? "mr-1" : ""}`}>
					<Icon name={icon} />
				</span>
			)}
			<>{children}</>
		</button>
	)
}

const Button = ({
	type = "default",
	icon,
	className,
	children = "",
	...rest
}: ButtonProps) => {
	switch (type) {
		case "menu-default":
			return (
				<Template
					aria-label="menu-default"
					className={className}
					defaultClassName="w-max py-2 px-5 hover:bg-menu dark:hover:bg-gray-800 rounded-md cursor-pointer focus:outline-hidden justify-center items-center text-xl tracking-wider flex text-gray-500 dark:text-gray-400"
					icon={icon}
					{...rest}>
					{children}
				</Template>
			)
		case "primary":
			return (
				<Template
					aria-label="primary"
					className={className}
					defaultClassName="w-max py-2 px-7 shadow-2xs border border-blue-500 dark:border-blue-900 dark:bg-blue-900 dark:text-gray-300 bg-blue-500 hover:bg-blue-600 hover:border-blue-600 dark:hover:bg-blue-800 dark:hover:border-blue-800 hover:shadow-inner text-white rounded-md cursor-pointer focus:outline-hidden justify-center items-center text-xl tracking-wider flex"
					icon={icon}
					{...rest}>
					{children}
				</Template>
			)
		case "menu-primary":
			return (
				<Template
					aria-label="menu-primary"
					className={className}
					defaultClassName="w-max py-2 px-5 hover:bg-pink-100 dark:hover:bg-pink-900 rounded-md cursor-pointer focus:outline-hidden justify-center items-center text-xl tracking-wider flex text-pink-500 dark:text-pink-400"
					icon={icon}
					{...rest}>
					{children}
				</Template>
			)
		default:
			return (
				<Template
					aria-label="default"
					className={className}
					defaultClassName="w-max py-2 px-7 shadow-2xs border border-gray-300 dark:border-gray-800 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 hover:shadow-inner rounded-md cursor-pointer focus:outline-hidden justify-center items-center text-xl tracking-wider bg-white flex"
					icon={icon}
					{...rest}>
					{children}
				</Template>
			)
	}
}

Button.displayName = "Button"

export default Button
