'use client'

import { ThemeProvider } from 'next-themes'
import { Provider as ReduxProvider } from 'react-redux'
import NextNprogress from 'nextjs-progressbar'
import Script from 'next/script'
import store from '~/store'
import Header from '~/components/Header'
import Footer from '~/components/Footer'

// Global stylesheets
import '~/assets/styles/vendors/tailwind.css'
import '~/styles/global.css'
import 'react-h5-audio-player/lib/styles.css'

const RootLayout = ({ children }: LayoutProps) => {
	return (
		<html lang="zh-cn">
			<head>
				<title>TonyHe</title>
			</head>
			<body>
				{/* Analytics Script */}
				<Script
					async
					defer
					data-do-not-track="true"
					data-domains="www.ouorz.com"
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
					enableSystem={true}
				>
					{/* Redux Store Provider */}
					<ReduxProvider store={store}>
						<Header />
						<main className="bg-gbg dark:bg-neutral-900 dark:text-white min-h-screen pb-20">
							<>{children}</>
						</main>
						<Footer />
					</ReduxProvider>
				</ThemeProvider>
			</body>
		</html>
	)
}

export default RootLayout
