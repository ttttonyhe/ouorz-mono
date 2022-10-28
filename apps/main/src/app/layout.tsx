'use client'

import { ThemeProvider } from 'next-themes'
import { Provider as ReduxProvider } from 'react-redux'
import NextNprogress from 'nextjs-progressbar'
import store from '~/store'
import Header from '~/components/Header'
import Footer from '~/components/Footer'

// Global stylesheets
import '~/assets/styles/vendors/tailwind.css'
import '~/styles/global.css'

const RootLayout = ({ children }: LayoutProps) => {
	return (
		<html lang="zh-cn">
			<body>
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
						<main className="bg-gbg dark:bg-neutral-900 dark:text-white min-h-screen">
							<Header />
							{children}
							<Footer />
						</main>
					</ReduxProvider>
				</ThemeProvider>
			</body>
		</html>
	)
}

export default RootLayout
