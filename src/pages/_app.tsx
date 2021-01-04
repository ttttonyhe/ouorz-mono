import '~/styles/tailwind.css'
import '~/styles/globals.css'
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
      <div className="bg-gbg dark:bg-black dark:text-white min-h-screen">
        <ThemeProvider attribute="class">
          <Component {...pageProps} />
        </ThemeProvider>
      </div>
    </div>
  )
}

export default App
