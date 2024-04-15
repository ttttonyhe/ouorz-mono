import Typography from "@tailwindcss/typography"
import type { Config } from "tailwindcss"

const config: Config = {
	content: [
		"./src/components/**/*.{js,ts,jsx,tsx,mdx}",
		"./src/app/**/*.{js,ts,jsx,tsx,mdx}",
	],
	darkMode: "class",
	theme: {
		extend: {
			height: {
				header: "2.875rem",
				footer: "2.875rem",
				main: "calc(100vh - 2.875rem)",
			},
			width: {
				sidebar: "17.5rem",
				"sidebar-lg": "12.5rem",
				article: "100%",
				"article-md": "calc(100% - 12.5rem)",
				"article-xl": "calc(100% - 17.5rem)",
			},
			padding: {
				header: "2.875rem",
			},
			margin: {
				header: "2.875rem",
				footer: "2.875rem",
				"sidebar-offset": "12.5rem",
			},
			colors: {
				"white-tinted": "#f6f7f8",
			},
			zIndex: {
				overlay: "28",
				header: "24",
				sidebar: "12",
				main: "20",
				footer: "16",
			},
			animation: {
				"aside-slide-in":
					"slide-in-from-left 0.3s cubic-bezier(0.15, 1, 0.3, 1) forwards",
				"article-appear": "fade-in 0.3s cubic-bezier(0.15, 1, 0.3, 1) forwards",
			},
			keyframes: {
				"slide-in-from-left": {
					"0%": {
						transform: "translateX(-100%)",
					},
					"100%": {
						transform: "translateX(0)",
					},
				},
				"fade-in": {
					"0%": {
						opacity: "0.5",
						transform: "translateY(0.1rem)",
					},
					"100%": {
						opacity: "1",
						transform: "translateY(0)",
					},
				},
			},
		},
	},
	plugins: [Typography],
}

export default config
