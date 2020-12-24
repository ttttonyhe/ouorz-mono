import '~/styles/tailwind.css'
import '~/styles/globals.css'
import type { AppProps } from 'next/app'
import NextNprogress from 'nextjs-progressbar'

function App({ Component, pageProps }: AppProps) {
  return (
    <div>
      <NextNprogress
        color="#D1D5DB"
        height={2}
        options={{ showSpinner: false }}
      />
      <div className="bg-gbg min-h-screen">
        <Component {...pageProps} />
      </div>
    </div>
  )
}

export default App
