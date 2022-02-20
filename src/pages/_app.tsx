import '~/assets/styles/vendors/tailwind.css'
import '~/styles/global.css'
import type { AppProps } from 'next/app'
import NextNprogress from 'nextjs-progressbar'
import { ThemeProvider } from 'next-themes'
import Script from 'next/script'

function App({ Component, pageProps }: AppProps) {
	return (
		<div>
			{/* Splitbee Analytics Script */}
			<Script async src="https://cdn.splitbee.io/sb.js" />
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
				<div className="bg-gbg dark:bg-neutral-900 dark:text-white min-h-screen">
					<Component {...pageProps} />
				</div>
			</ThemeProvider>
		</div>
	)
}

export default App
