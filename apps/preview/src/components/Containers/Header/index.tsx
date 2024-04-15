"use client"

import { useTheme } from "next-themes"

const Header = () => {
	const { theme, setTheme } = useTheme()
	return (
		<header className="relative z-header flex h-header w-full border-b dark:border-neutral-800 dark:bg-neutral-900">
			<div>
				<h1>Tony He</h1>
				<button onClick={() => setTheme(theme === "light" ? "dark" : "light")}>
					Change theme
				</button>
			</div>
		</header>
	)
}

export default Header
