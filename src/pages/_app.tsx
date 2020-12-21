import { GlobalStyles } from 'twin.macro'
import '~/styles/global.css'
import type { AppProps } from 'next/app'

function App({ Component, pageProps }: AppProps) {
  return (
    <div>
      <GlobalStyles />
      <Component {...pageProps} />
    </div>
  )
}

export default App
