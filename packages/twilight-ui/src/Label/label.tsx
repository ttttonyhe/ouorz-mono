import Icon from "../Icon"
import type { LabelTypes, IconNames } from "../utils/propTypes"
import React from "react"

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

const Label = ({
	type = "primary",
	icon,
	children = "Label",
	preview = false,
	className,
	iconClassName,
	...props
}: LabelProps) => {
	switch (type) {
		case "primary":
			return (
				<label
					{...props}
					className="effect-pressing flex w-auto cursor-pointer items-center justify-center rounded-md bg-blue-100 px-2 py-1 text-center align-middle text-4 font-medium tracking-wide text-blue-500 hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-300 dark:hover:bg-blue-800 lg:px-4 lg:py-1 lg:text-label">
					{icon && (
						<span className="mr-1 h-4 w-4 lg:mr-2 lg:h-7 lg:w-7">
							<Icon name={icon} />
						</span>
					)}
					<>{children}</>
				</label>
			)
		case "secondary":
			return (
				<label
					{...props}
					className="effect-pressing flex w-auto cursor-pointer items-center justify-center rounded-md bg-gray-100 px-2 py-1 text-center align-middle text-4 font-medium tracking-wide text-gray-500 hover:bg-gray-200 focus:animate-pulse dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 lg:px-4 lg:py-1 lg:text-label">
					{icon && (
						<span className="mr-1 h-4 w-4 lg:mr-2 lg:h-7 lg:w-7">
							<Icon name={icon} />
						</span>
					)}
					<>{children}</>
				</label>
			)
		case "green":
			return (
				<label
					className={`group flex h-full w-min cursor-pointer items-center justify-center gap-x-1 font-medium ${
						preview ? "px-3 py-0.5" : "px-4 py-1.5"
					} effect-pressing rounded-md bg-green-100 text-center align-middle text-xl tracking-wide text-green-500 hover:bg-green-200 dark:bg-green-800 dark:text-green-400 dark:hover:bg-green-700`}>
					<>{children}</>
					{icon && (
						<span className="-ml-5 -mr-1 h-4 w-4 opacity-0 transition-all ease-in-out group-hover:ml-0 group-hover:mr-0 group-hover:opacity-100 lg:h-[19px] lg:w-[19px]">
							<Icon name={icon} />
						</span>
					)}
				</label>
			)
		case "sticky-icon":
			return (
				<label
					{...props}
					className="flex h-auto w-auto items-center justify-center rounded-md bg-yellow-200 px-2 py-0 text-center align-middle text-4 tracking-wide text-yellow-500 hover:bg-yellow-300 dark:bg-yellow-800 dark:hover:bg-yellow-700 lg:px-3 lg:py-1 lg:text-label">
					<span className="h-4 w-4 lg:h-7 lg:w-7">
						<Icon name="sticky" />
					</span>
				</label>
			)
		case "gray-icon":
			return (
				<label
					{...props}
					className="effect-pressing flex h-full w-min cursor-pointer items-center justify-center rounded-md bg-gray-100 px-2 py-2 text-center align-middle text-xl font-medium tracking-wide text-gray-500 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-500">
					{icon && (
						<span className="h-4 w-4 lg:h-[19px] lg:w-[19px]">
							<Icon name={icon} />
						</span>
					)}
				</label>
			)
		case "green-icon":
			return (
				<label
					{...props}
					className={`effect-pressing flex h-full w-min cursor-pointer items-center justify-center rounded-md bg-green-100 px-2 py-2 text-center align-middle text-xl font-medium tracking-wide text-green-500 hover:bg-green-200 dark:bg-green-700 dark:text-green-300 dark:hover:bg-green-600 ${
						className ?? ""
					}`}>
					{icon && (
						<span className="h-4 w-4 lg:h-[19px] lg:w-[19px]">
							<Icon name={icon} />
						</span>
					)}
				</label>
			)
		case "orange-icon":
			return (
				<label
					{...props}
					className="effect-pressing flex h-full w-min cursor-pointer items-center justify-center rounded-md bg-orange-100 px-2 py-2 text-center align-middle text-xl font-medium tracking-wide text-orange-500 hover:bg-orange-200 dark:bg-orange-700 dark:text-orange-300 dark:hover:bg-orange-600">
					{icon && (
						<span
							className={`h-4 w-4 lg:h-[19px] lg:w-[19px] ${iconClassName}`}>
							<Icon name={icon} />
						</span>
					)}
				</label>
			)
	}
}

Label.displayName = "Label"

export default Label
