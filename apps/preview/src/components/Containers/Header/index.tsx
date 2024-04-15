"use client"

import responsive from "@/styles/responsive.module.css"
import cn from "clsx"
import { useTheme } from "next-themes"

const Header = () => {
	const { theme, setTheme } = useTheme()
	return (
		<header className="relative z-header flex h-header">
			<div
				className={cn(
					responsive["sidebar-width"],
					"sidebar flex-shrink-0 bg-yellow-500"
				)}>
				<h1>Tony He</h1>
				<button onClick={() => setTheme(theme === "light" ? "dark" : "light")}>
					Change theme
				</button>
			</div>
			<div className="w-full bg-blue-500">
				<span>k-bar</span>
			</div>
		</header>
	)
}

export default Header
