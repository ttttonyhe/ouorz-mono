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
					<meta name="robots" content="index,follow" />
					<meta name="googlebot" content="index,follow" />
					<meta name="twitter:card" content="summary_large_image" />
					<meta name="twitter:site" content="@ttttonyhe" />
					<meta name="twitter:creator" content="@ttttonyhe" />
					<meta property="og:url" content="https://www.ouorz.com" />
					<meta property="og:type" content="website" />
					<meta property="og:title" content="TonyHe" />
					<meta
						property="og:description"
						content="Developer, blogger, podcaster"
					/>
					<meta
						property="og:image"
						content="https://static.ouorz.com/ouorz-og-image.jpg"
					/>
					<meta property="og:image:alt" content="TonyHe" />
					<meta property="og:locale" content="zh_CN" />
					<meta property="og:site_name" content="TonyHe" />
					<meta name="theme-color" content="#f7f8f9" />
					<link
						rel="apple-touch-icon"
						href="https://static.ouorz.com/tonyhe_rounded_apple_touch.png"
					/>
					<link
						rel="mask-icon"
						href="https://static.ouorz.com/ouorz-mask.ico"
					/>
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
