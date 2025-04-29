import type { NextPage } from "next"
import { ThemeProvider } from "next-themes"
import type { AppProps } from "next/app"
import Script from "next/script"
import NextNprogress from "nextjs-progressbar"
import type { ReactElement, ReactNode } from "react"
import { Provider as ReduxProvider } from "react-redux"
import store from "~/store"
import "~/styles/global.css"

export type NextPageWithLayout = NextPage & {
	layout?: (page: ReactElement) => ReactNode
}

type AppPropsWithLayout = AppProps & {
	Component: NextPageWithLayout
}

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
			<ThemeProvider
				attribute="class"
				defaultTheme="system"
				enableSystem={true}>
				{/* Redux Store Provider */}
				<ReduxProvider store={store}>
					<div className="min-h-screen animate-appear bg-gbg dark:bg-neutral-900 dark:text-white">
						<>{getLayout(<Component {...pageProps} />)}</>
					</div>
				</ReduxProvider>
			</ThemeProvider>
		</div>
	)
}

export default App
