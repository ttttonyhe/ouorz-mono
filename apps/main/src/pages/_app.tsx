import "@twilight-toolkit/ui/styles/base.css"
import "highlight.js/styles/github-dark.css"
import "katex/dist/katex.min.css"
import type { NextPage } from "next"
import { ThemeProvider } from "next-themes"
import type { AppProps } from "next/app"
import { useRouter } from "next/router"
import Script from "next/script"
import NextNprogress from "nextjs-progressbar"
import type { ReactElement, ReactNode } from "react"
import { Provider as ReduxProvider } from "react-redux"

import SiteSeo from "~/components/SiteSeo"
import store from "~/store"
import { getPageMetadata } from "~/utilities/seo"
import type { SeoPageProps } from "~/utilities/seo"

import "~/styles/global.css"
import { useViewTransitionRouter } from "~/utilities/viewTransition"

export type NextPageWithLayout<Props = Record<string, never>> =
	NextPage<Props> & {
		layout?: (page: ReactElement) => ReactNode
	}

type AppPropsWithLayout = AppProps<SeoPageProps> & {
	Component: NextPageWithLayout<SeoPageProps>
}

function App({ Component, pageProps }: AppPropsWithLayout) {
	const router = useRouter()
	const getLayout = Component.layout ?? ((page) => page)

	useViewTransitionRouter(router)

	return (
		<div>
			<SiteSeo metadata={getPageMetadata(router.pathname, pageProps)} />
			<Script
				async
				defer
				data-do-not-track="true"
				data-domains="lipeng.ac"
				data-website-id="e3d939fa-1fa0-4c06-adb1-1081d6b6686e"
				src="https://analytics.ouorz.com/analytics.js"
			/>
			<NextNprogress
				color="#d4d4d8"
				height={2}
				options={{ showSpinner: false }}
			/>
			<ThemeProvider attribute="class" defaultTheme="light" enableSystem={true}>
				<ReduxProvider store={store}>
					<div className="min-h-screen animate-appear bg-gbg dark:bg-neutral-900 dark:text-white">
						{getLayout(<Component {...pageProps} />)}
					</div>
				</ReduxProvider>
			</ThemeProvider>
		</div>
	)
}

export default App
