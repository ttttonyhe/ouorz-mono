import { Icon } from "@twilight-toolkit/ui"
import React from "react"
import { LabelTypes } from "~/constants/propTypes"

interface LabelProps {
	type: LabelTypes
	icon?: string
	children?: React.ReactNode
	preview?: boolean
}

export default function Label({ type, icon, children, preview }: LabelProps) {
	switch (type) {
		case "sticky":
			return (
				<label className="flex h-auto w-auto items-center justify-center rounded-md bg-yellow-200 px-2 py-0 text-center align-middle text-4 tracking-wide text-yellow-500 hover:bg-yellow-300 dark:bg-yellow-800 dark:hover:bg-yellow-700 lg:px-3 lg:py-1 lg:text-label">
					<span className="h-4 w-4 lg:h-7 lg:w-7">
						<Icon name="sticky" />
					</span>
				</label>
			)
		case "primary":
			return (
				<label className="duration-50 flex w-auto cursor-pointer items-center justify-center rounded-md bg-blue-100 px-2 py-1 text-center align-middle text-4 font-medium tracking-wide text-blue-500 transition-transform ease-linear hover:bg-blue-200 active:translate-y-[0.5px] active:scale-[0.985] dark:bg-blue-900 dark:text-blue-300 dark:hover:bg-blue-800 lg:px-4 lg:py-1 lg:text-label">
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
				<label className="duration-50 flex w-auto cursor-pointer items-center justify-center rounded-md bg-gray-100 px-2 py-1 text-center align-middle text-4 font-medium tracking-wide text-gray-500 transition-transform ease-linear hover:bg-gray-200 focus:animate-pulse active:translate-y-[0.5px] active:scale-[0.985] dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 lg:px-4 lg:py-1 lg:text-label">
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
					className={`group flex h-full w-min cursor-pointer items-center justify-center font-medium ${
						preview ? "px-3 py-0.5" : "px-4 py-1.5"
					} duration-50 rounded-md bg-green-100 text-center align-middle text-xl tracking-wide text-green-500 transition-transform ease-linear hover:bg-green-200 active:translate-y-[0.5px] active:scale-[0.985] dark:bg-green-800 dark:text-green-400 dark:hover:bg-green-700`}>
					<>{children}</>
					{icon && (
						<span className="group-hover:animate-pointer ml-1 h-5 w-5">
							<Icon name={icon} />
						</span>
					)}
				</label>
			)
		case "gray":
			return (
				<label className="duration-50 flex h-full w-min cursor-pointer items-center justify-center rounded-md bg-gray-100 px-2 py-2 text-center align-middle text-xl font-medium tracking-wide text-gray-500 transition-transform ease-linear hover:bg-gray-200 active:translate-y-[0.5px] active:scale-[0.985] dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
					{icon && (
						<span className="h-7 w-7">
							<Icon name={icon} />
						</span>
					)}
				</label>
			)
	}
}
