import type { NextPage } from "next"
import { ThemeProvider } from "next-themes"
import type { AppProps } from "next/app"
import { Noto_Serif_SC } from "next/font/google"
import localFont from "next/font/local"
import Script from "next/script"
import NextNprogress from "nextjs-progressbar"
import type { ReactElement, ReactNode } from "react"
import { Provider as ReduxProvider } from "react-redux"
import "~/assets/styles/vendors/tailwind.css"
import store from "~/store"
import "~/styles/global.css"

export type NextPageWithLayout = NextPage & {
	layout?: (page: ReactElement) => ReactNode
}

type AppPropsWithLayout = AppProps & {
	Component: NextPageWithLayout
}

const notoSerifSCFont = Noto_Serif_SC({
	subsets: ["latin"],
})

const minion3Font = localFont({
	src: [
		{
			path: "./fonts/Minion3-Regular.woff2",
			weight: "normal",
			style: "normal",
		},
		{
			path: "./fonts/Minion3-Italic.woff2",
			weight: "normal",
			style: "italic",
		},
		{
			path: "./fonts/Minion3-Medium.woff2",
			weight: "500",
			style: "normal",
		},
		{
			path: "./fonts/Minion3-MediumItalic.woff2",
			weight: "500",
			style: "italic",
		},
		{
			path: "./fonts/Minion3-Semibold.woff2",
			weight: "600",
			style: "normal",
		},
		{
			path: "./fonts/Minion3-SemiboldItalic.woff2",
			weight: "600",
			style: "italic",
		},
		{
			path: "./fonts/Minion3-Bold.woff2",
			weight: "bold",
			style: "normal",
		},
		{
			path: "./fonts/Minion3-BoldItalic.woff2",
			weight: "bold",
			style: "italic",
		},
	],
	adjustFontFallback: false,
})

function App({ Component, pageProps }: AppPropsWithLayout) {
	const getLayout = Component.layout ?? ((page) => page)

	return (
		<div>
			{/* Analytics Script */}
			<Script
				async
				defer
				data-do-not-track="true"
				data-domains="lipeng.ac"
				data-website-id="e3d939fa-1fa0-4c06-adb1-1081d6b6686e"
				src="https://analytics.ouorz.com/analytics.js"
			/>
			{/* NProgress Loading Progress Bar */}
			<NextNprogress
				color="#d4d4d8"
				height={2}
				options={{ showSpinner: false }}
			/>
			{/* Next-Themes Theme Provider */}
			<ThemeProvider attribute="class" defaultTheme="light" enableSystem={true}>
				{/* Redux Store Provider */}
				<ReduxProvider store={store}>
					<div
						className="min-h-screen animate-appear bg-gbg dark:bg-neutral-900 dark:text-white"
						style={{
							fontFamily: `${minion3Font.style.fontFamily}, ${notoSerifSCFont.style.fontFamily}`,
						}}>
						<>{getLayout(<Component {...pageProps} />)}</>
					</div>
				</ReduxProvider>
			</ThemeProvider>
		</div>
	)
}

export default App
