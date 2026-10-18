import React from "react"

import Icon from "../Icon"
import type { ButtonTypes, IconNames } from "../utils/propTypes"

interface Props {
	type?: ButtonTypes
	icon?: IconNames
	className?: string
	children?: React.ReactNode
}

export type ButtonProps = Props &
	(
		| ({ href: string } & Omit<
				React.AnchorHTMLAttributes<HTMLAnchorElement>,
				keyof Props
		  >)
		| Omit<React.ButtonHTMLAttributes<HTMLButtonElement>, keyof Props>
	)

const buttonClasses = {
	"menu-default":
		"w-max py-2 px-5 hover:bg-menu dark:hover:bg-gray-800 rounded-md cursor-pointer focus:outline-hidden justify-center items-center text-xl tracking-wider flex text-gray-500 dark:text-gray-400",
	primary:
		"w-max py-2 px-7 shadow-2xs border border-blue-500 dark:border-blue-900 dark:bg-blue-900 dark:text-gray-300 bg-blue-500 hover:bg-blue-600 hover:border-blue-600 dark:hover:bg-blue-800 dark:hover:border-blue-800 hover:shadow-inner text-white rounded-md cursor-pointer focus:outline-hidden justify-center items-center text-xl tracking-wider flex",
	"menu-primary":
		"w-max py-2 px-5 hover:bg-pink-100 dark:hover:bg-pink-900 rounded-md cursor-pointer focus:outline-hidden justify-center items-center text-xl tracking-wider flex text-pink-500 dark:text-pink-400",
	default:
		"w-max py-2 px-7 shadow-2xs border border-gray-300 dark:border-gray-800 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 hover:shadow-inner rounded-md cursor-pointer focus:outline-hidden justify-center items-center text-xl tracking-wider bg-white flex",
} satisfies Record<ButtonTypes, string>

const Button = ({
	type = "default",
	icon,
	className,
	children = "",
	...rest
}: ButtonProps) => {
	const classes = `${className ? `${className} ` : ""}${buttonClasses[type]} effect-pressing`
	const content = (
		<>
			{icon && (
				<span className={`h-6 w-6 shrink-0 ${children ? "mr-1" : ""}`}>
					<Icon name={icon} />
				</span>
			)}
			{children}
		</>
	)

	if ("href" in rest) {
		return (
			<a {...rest} className={classes}>
				{content}
			</a>
		)
	}

	return (
		<button {...rest} type="button" className={classes}>
			{content}
		</button>
	)
}

Button.displayName = "Button"

export default Button
