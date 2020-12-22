import '~/styles/tailwind.css'
import '~/styles/globals.css'
import type { AppProps } from 'next/app'

function App({ Component, pageProps }: AppProps) {
  return (
    <div className="bg-gbg min-h-screen">
      <Component {...pageProps} />
    </div>
  )
}

export default App
