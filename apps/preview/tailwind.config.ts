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
			},
			margin: {
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
		},
	},
	plugins: [Typography],
}

export default config
