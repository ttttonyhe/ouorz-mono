import Typography from "@tailwindcss/typography"
import type { Config } from "tailwindcss"

const config: Config = {
	// MDX files may contain Tailwind CSS classes
	content: [
		"./src/components/**/*.{js,ts,jsx,tsx,mdx}",
		"./src/app/**/*.{js,ts,jsx,tsx,mdx}",
	],
	// Use with next-themes
	darkMode: "class",
	theme: {
		extend: {
			height: {
				header: "50px",
				footer: "3rem",
				main: "calc(100vh - 3.125rem)",
			},
			width: {
				sidebar: "17.5rem",
				"sidebar-lg": "12.5rem",
				article: "100%",
				"article-md": "calc(100% - 12.5rem)",
				"article-xl": "calc(100% - 17.5rem)",
				"article-content": "40rem",
			},
			padding: {
				header: "3.125rem",
			},
			spacing: {
				sidebar: "17.5rem",
			},
			margin: {
				header: "3.125rem",
				footer: "3rem",
				"sidebar-offset": "12.5rem",
			},
			colors: {
				"white-tinted": "#f6f7f8",
			},
			zIndex: {
				overlay: "28",
				header: "24",
				sidebar: "16",
				panel: "12",
				article: "20",
				footer: "24",
			},
			animation: {
				"panel-slide-in":
					"slide-in-from-left 0.3s cubic-bezier(0.15, 1, 0.3, 1) forwards",
				"panel-slide-out":
					"slide-out-from-right 0.3s cubic-bezier(0.15, 1, 0.3, 1) forwards",
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
				"slide-out-from-right": {
					"0%": {
						transform: "translateX(0%)",
					},
					"100%": {
						transform: "translateX(-100%)",
					},
				},
				"fade-in": {
					"0%": {
						opacity: "0.5",
						transform: "translateY(0.2rem)",
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
