import '~/assets/styles/vendors/tailwind.css'
import '~/global.css'
import type { AppProps } from 'next/app'
import NextNprogress from 'nextjs-progressbar'
import { ThemeProvider } from 'next-themes'

function App({ Component, pageProps }: AppProps) {
  return (
    <div>
      <NextNprogress
        color="#D1D5DB"
        height={2}
        options={{ showSpinner: false }}
      />
      <ThemeProvider
        attribute="class"
        defaultTheme="system"
        enableSystem={true}
      >
        <div className="bg-gbg dark:bg-black dark:text-white min-h-screen">
          <Component {...pageProps} />
        </div>
      </ThemeProvider>
    </div>
  )
}

export default App
