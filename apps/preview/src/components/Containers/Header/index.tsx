"use client"

import responsive from "@/styles/responsive.module.css"
import cn from "clsx"
import { useTheme } from "next-themes"

const Header = () => {
	const { theme, setTheme } = useTheme()
	return (
		<header className="relative z-header flex h-header dark:bg-neutral-900">
			<div
				className={cn(
					responsive["sidebar-width"],
					"sidebar flex-shrink-0 border-b border-r dark:border-neutral-800"
				)}>
				<h1>Tony He</h1>
				<button onClick={() => setTheme(theme === "light" ? "dark" : "light")}>
					Change theme
				</button>
			</div>
			<div className="flex w-full items-center justify-center border-b dark:border-neutral-800">
				<div className="h-[2.125rem] w-1/3 rounded-md border dark:border-neutral-700" />
			</div>
		</header>
	)
}

export default Header
