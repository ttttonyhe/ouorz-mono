import Drawer from "@/components/Containers/Drawer"
import ThemeProvider from "@/providers/themeProvider"

import "@/styles/globals.css"
import type { Metadata, Viewport } from "next"
import type { FC, PropsWithChildren, ReactNode } from "react"

export const metadata: Metadata = {
	metadataBase: new URL("https://preview.ouorz.com"),
	title: "Tony He (Preview)",
	description: "Student Researcher / Software Engineer",
	keywords: [
		"Lipeng He",
		"Tony He",
		"Researcher",
		"Software Engineer",
		"贺莉朋",
		"博客",
		"个人博客",
		"独立博客",
		"前端开发",
		"后端开发",
		"全栈开发",
		"区块链",
		"工程师",
		"研究学者",
		"Crypto",
		"Blockchain",
		"Tony",
		"Developer",
		"Blogger",
		"Podcaster",
		"Blog",
		"Personal Site",
		"Next.js",
		"React.js",
		"TypeScript",
		"JavaScript",
	],
	alternates: {
		canonical: "https://lipeng.ac",
	},
	robots: {
		index: true,
		follow: true,
	},
	icons: {
		icon: [
			{
				url: "/icon-dark.png",
				media: "(prefers-color-scheme: dark)",
			},
			{
				url: "/icon.png",
				media: "(prefers-color-scheme: light)",
			},
		],
		apple: "https://static.ouorz.com/tonyhe_rounded_apple_touch.png",
		other: [
			{
				rel: "mask-icon",
				url: "https://static.ouorz.com/ouorz-mask.ico",
			},
		],
	},
	openGraph: {
		url: "https://preview.ouorz.com",
		type: "website",
		title: "Tony He",
		description: "Student Researcher / Software Engineer",
		siteName: "TonyHe",
		locale: "zh_CN",
		images: [
			{
				url: "https://static.ouorz.com/ouorz-og-image.jpg",
				alt: "TonyHe",
			},
		],
	},
	twitter: {
		card: "summary_large_image",
		site: "@ttttonyhe",
		creator: "@ttttonyhe",
		title: "TonyHe",
		description: "Student Researcher / Software Engineer",
		images: ["https://static.ouorz.com/ouorz-og-image.jpg"],
	},
}

export const viewport: Viewport = {
	themeColor: "#f7f8f9",
}

interface RootLayoutProps extends PropsWithChildren {
	sidebar: ReactNode
	main: ReactNode
	aside: ReactNode
}

const RootLayout: FC<RootLayoutProps> = ({ sidebar, main, aside }) => {
	return (
		<html lang="zh-cn" suppressHydrationWarning>
			<body className="h-screen bg-white dark:bg-black">
				<ThemeProvider>
					<main className="flex h-screen">
						{sidebar}
						<section className="relative h-screen w-full">{main}</section>
						{aside}
					</main>
					<Drawer />
				</ThemeProvider>
			</body>
		</html>
	)
}

export default RootLayout
