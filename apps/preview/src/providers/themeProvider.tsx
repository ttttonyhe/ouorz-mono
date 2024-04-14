"use client"

import { THEME_STORAGE_KEY } from "@/constants/themes"
import { ThemeProvider as NextThemeProvider } from "next-themes"
import type { FC, PropsWithChildren } from "react"

const ThemeProvider: FC<PropsWithChildren> = ({ children }) => {
	return (
		<NextThemeProvider
			attribute="class"
			defaultTheme="light"
			storageKey={THEME_STORAGE_KEY}
			enableSystem={true}>
			{children}
		</NextThemeProvider>
	)
}

export default ThemeProvider
