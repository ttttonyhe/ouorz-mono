import type React from "react"

import Icon from "../Icon"
import type { IconNames, LabelTypes } from "../utils/propTypes"

interface Props {
	type: LabelTypes
	icon?: IconNames
	children?: React.ReactNode
	preview?: boolean
	iconClassName?: string
}

type NativeAttrs = Omit<React.HTMLAttributes<HTMLSpanElement>, keyof Props>
export type LabelProps = Props & NativeAttrs

const joinClasses = (base: string, extra?: string) =>
	extra ? `${base} ${extra}` : base

const Label = ({
	type = "primary",
	icon,
	children = "Label",
	preview = false,
	className,
	iconClassName,
	style,
	...props
}: LabelProps) => {
	switch (type) {
		case "primary":
			return (
				<span
					{...props}
					style={style}
					className={joinClasses(
						"effect-pressing flex w-auto cursor-pointer items-center justify-center rounded-md bg-blue-100 px-2 py-1 text-center align-middle text-4 font-medium tracking-wide text-blue-500 hover:bg-blue-200 lg:px-4 lg:py-1 lg:text-label dark:bg-blue-900 dark:text-blue-300 dark:hover:bg-blue-800",
						className
					)}>
					{icon && (
						<span className="mr-1 h-4 w-4 lg:mr-2 lg:h-7 lg:w-7">
							<Icon name={icon} />
						</span>
					)}
					{children}
				</span>
			)
		case "secondary":
			return (
				<span
					{...props}
					style={style}
					className={joinClasses(
						"effect-pressing flex w-auto cursor-pointer items-center justify-center rounded-md bg-gray-100 px-2 py-1 text-center align-middle text-4 font-medium tracking-wide text-gray-500 hover:bg-gray-200 focus:animate-pulse lg:px-4 lg:py-1 lg:text-label dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600",
						className
					)}>
					{icon && (
						<span className="mr-1 h-4 w-4 lg:mr-2 lg:h-7 lg:w-7">
							<Icon name={icon} />
						</span>
					)}
					{children}
				</span>
			)
		case "green":
			return (
				<span
					{...props}
					style={style}
					className={joinClasses(
						`group flex h-full w-min cursor-pointer items-center justify-center gap-x-1 font-medium ${
							preview ? "px-3 py-0.5" : "px-4 py-1.5"
						} effect-pressing rounded-md bg-green-100 text-center align-middle text-xl tracking-wide text-green-500 hover:bg-green-200 dark:bg-green-800 dark:text-green-400 dark:hover:bg-green-700`,
						className
					)}>
					{children}
					{icon && (
						<span className="-mr-1 -ml-5 h-4 w-4 opacity-0 transition-all ease-in-out group-hover:mr-0 group-hover:ml-0 group-hover:opacity-100 lg:h-[19px] lg:w-[19px]">
							<Icon name={icon} />
						</span>
					)}
				</span>
			)
		case "sticky-icon":
			return (
				<span
					{...props}
					style={style}
					className={joinClasses(
						"flex h-auto w-auto items-center justify-center rounded-md bg-yellow-200 px-2 py-0 text-center align-middle text-4 tracking-wide text-yellow-500 hover:bg-yellow-300 lg:px-3 lg:py-1 lg:text-label dark:bg-yellow-800 dark:hover:bg-yellow-700",
						className
					)}>
					<span className="h-4 w-4 lg:h-7 lg:w-7">
						<Icon name="sticky" />
					</span>
				</span>
			)
		case "gray-icon":
			return (
				<span
					{...props}
					style={style}
					className={joinClasses(
						"effect-pressing flex h-full w-min cursor-pointer items-center justify-center rounded-md bg-gray-100 px-2 py-2 text-center align-middle text-xl font-medium tracking-wide text-gray-500 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-500",
						className
					)}>
					{icon && (
						<span className="h-4 w-4 lg:h-[19px] lg:w-[19px]">
							<Icon name={icon} />
						</span>
					)}
				</span>
			)
		case "green-icon":
			return (
				<span
					{...props}
					style={style}
					className={joinClasses(
						"effect-pressing flex h-full w-min cursor-pointer items-center justify-center rounded-md bg-green-100 px-2 py-2 text-center align-middle text-xl font-medium tracking-wide text-green-500 hover:bg-green-200 dark:bg-green-700 dark:text-green-300 dark:hover:bg-green-600",
						className
					)}>
					{icon && (
						<span className="h-4 w-4 lg:h-[19px] lg:w-[19px]">
							<Icon name={icon} />
						</span>
					)}
				</span>
			)
		case "orange-icon":
			return (
				<span
					{...props}
					style={style}
					className={joinClasses(
						"effect-pressing flex h-full w-min cursor-pointer items-center justify-center rounded-md bg-orange-100 px-2 py-2 text-center align-middle text-xl font-medium tracking-wide text-orange-500 hover:bg-orange-200 dark:bg-orange-700 dark:text-orange-300 dark:hover:bg-orange-600",
						className
					)}>
					{icon && (
						<span
							className={`h-4 w-4 lg:h-[19px] lg:w-[19px] ${iconClassName ?? ""}`}>
							<Icon name={icon} />
						</span>
					)}
				</span>
			)
	}
}

Label.displayName = "Label"

export default Label
