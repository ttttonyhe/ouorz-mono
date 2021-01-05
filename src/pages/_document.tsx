import Document, {
  Html,
  Head,
  Main,
  NextScript,
  DocumentContext,
} from 'next/document'

class MyDocument extends Document {
  static async getInitialProps(ctx: DocumentContext) {
    const initialProps = await Document.getInitialProps(ctx)

    return initialProps
  }
  render() {
    return (
      <Html lang="zh-cn">
        <Head>
          <script
            async
            src="https://www.googletagmanager.com/gtag/js?id=UA-163998158-1"
          ></script>
          <script
            async
            dangerouslySetInnerHTML={{
              __html: `window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', 'UA-163998158-1');`,
            }}
          ></script>
          <meta name="twitter:card" content="summary_large_image"></meta>
          <meta name="twitter:site" content="@ttttonyhe"></meta>
          <meta name="twitter:creator" content="@ttttonyhe"></meta>
          <meta property="og:url" content="https://www.ouorz.com"></meta>
          <meta property="og:type" content="website"></meta>
          <meta property="og:title" content="TonyHe"></meta>
          <meta
            property="og:description"
            content="Developer, blogger, podcaster"
          ></meta>
          <meta
            property="og:image"
            content="https://static.ouorz.com/ouorz-og-image.jpg"
          ></meta>
          <meta property="og:image:alt" content="TonyHe"></meta>
          <meta property="og:locale" content="zh_CN"></meta>
          <meta property="og:site_name" content="TonyHe"></meta>
          <meta name="theme-color" content="#f7f8f9"></meta>
          <link
            rel="apple-touch-icon"
            href="https://static.ouorz.com/tonyhe_rounded_apple_touch.png"
          ></link>
          <link
            rel="mask-icon"
            href="https://static.ouorz.com/ouorz-mask.ico"
          ></link>
        </Head>

        <body>
          <Main />
          <NextScript />
        </body>
      </Html>
    )
  }
}

export default MyDocument
