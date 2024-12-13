import Drawer from "@/components/Containers/Drawer"
import ThemeProvider from "@/providers/themeProvider"
import "@/styles/globals.css"
import type { Metadata } from "next"
import type { FC, PropsWithChildren, ReactNode } from "react"

export const metadata: Metadata = {
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
		"WordPress",
		"Next.js",
		"React.js",
		"TypeScript",
		"JavaScript",
	],
}

interface RootLayoutProps extends PropsWithChildren {
	sidebar: ReactNode
	main: ReactNode
	aside: ReactNode
}

const RootLayout: FC<RootLayoutProps> = ({ sidebar, main, aside }) => {
	return (
		<html lang="zh-cn" suppressHydrationWarning>
			<head>
				{/* Dark mode favicons */}
				<link
					rel="icon"
					type="image/x-icon"
					href="/icon-dark.png"
					media="(prefers-color-scheme: dark)"
				/>
				<link
					type="image/vnd.microsoft.icon"
					href="/icon-dark.png"
					rel="shortcut icon"
					media="(prefers-color-scheme: dark)"
				/>
				{/* Light mode favicons */}
				<link
					rel="icon"
					type="image/x-icon"
					href="/icon.png"
					media="(prefers-color-scheme: light)"
				/>
				<link
					type="image/vnd.microsoft.icon"
					href="/icon.png"
					rel="shortcut icon"
					media="(prefers-color-scheme: light)"
				/>
				<meta name="robots" content="index,follow" />
				<meta name="googlebot" content="index,follow" />
				<meta property="og:url" content="https://preview.ouorz.com" />
				<meta property="og:type" content="website" />
				<meta property="og:title" content="Tony He" />
				<meta
					property="og:description"
					content="Student Researcher / Software Engineer"
				/>
				<meta
					property="og:image"
					content="https://static.ouorz.com/ouorz-og-image.jpg"
				/>
				<meta property="og:image:alt" content="TonyHe" />
				<meta property="og:locale" content="zh_CN" />
				<meta property="og:site_name" content="TonyHe" />
				<meta name="twitter:card" content="summary_large_image" />
				<meta name="twitter:site" content="@ttttonyhe" />
				<meta name="twitter:creator" content="@ttttonyhe" />
				<meta property="twitter:title" content="TonyHe" />
				<meta
					property="twitter:image"
					content="https://static.ouorz.com/ouorz-og-image.jpg"
				/>
				<meta
					property="twitter:description"
					content="Student Researcher / Software Engineer"
				/>
				<meta name="theme-color" content="#f7f8f9" />
				<link rel="canonical" href="https://www.ouorz.com" />
				<link
					rel="apple-touch-icon"
					href="https://static.ouorz.com/tonyhe_rounded_apple_touch.png"
				/>
				<link rel="mask-icon" href="https://static.ouorz.com/ouorz-mask.ico" />
			</head>
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
