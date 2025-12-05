import Document, {
	type DocumentContext,
	type DocumentInitialProps,
	Head,
	Html,
	Main,
	NextScript,
} from "next/document"

class AppDocument extends Document {
	static async getInitialProps(
		ctx: DocumentContext
	): Promise<DocumentInitialProps> {
		const initialProps = await Document.getInitialProps(ctx)
		return initialProps
	}

	render() {
		return (
			<Html lang="en" prefix="og: http://ogp.me/ns#">
				<Head>
					<link rel="icon" type="image/x-icon" href="/favicon.ico" />
					<link
						type="image/vnd.microsoft.icon"
						href="/favicon-dark.ico"
						rel="shortcut icon"
						media="(prefers-color-scheme: dark)"
					/>
					<link
						type="image/vnd.microsoft.icon"
						href="/favicon.ico"
						rel="shortcut icon"
						media="(prefers-color-scheme: light)"
					/>
					<meta
						name="description"
						content="Tony (Lipeng) He (贺莉朋) is a researcher and software engineer."
					/>
					<meta
						name="keywords"
						content="Tony He, Lipeng He, 贺莉朋, 工程师, 研究学者, Researcher, Software Engineer, Tony"
					/>
					<meta name="robots" content="index,follow" />
					<meta name="googlebot" content="index,follow" />
					<meta property="og:url" content="https://lipeng.ac" />
					<meta property="og:type" content="website" />
					<meta property="og:title" content="Tony He" />
					<meta
						property="og:description"
						content="Tony (Lipeng) He is a researcher and software engineer."
					/>
					<meta
						property="og:image"
						content="https://static.ouorz.com/ouorz-og-image.jpg"
					/>
					<meta property="og:image:alt" content="TonyHe" />
					<meta property="og:locale" content="zh_CN" />
					<meta property="og:site_name" content="TonyHe" />
					<meta name="twitter:card" content="summary_large_image" />
					<meta name="twitter:site" content="@ttttonyhe" />
					<meta name="twitter:creator" content="@ttttonyhe" />
					<meta property="twitter:title" content="TonyHe" />
					<meta
						property="twitter:image"
						content="https://static.ouorz.com/ouorz-og-image.jpg"
					/>
					<meta
						property="twitter:description"
						content="Tony (Lipeng) He is a researcher and software engineer."
					/>
					<meta name="theme-color" content="#f7f8f9" />
					<link rel="canonical" href="https://lipeng.ac" />
					<link
						rel="apple-touch-icon"
						href="https://static.ouorz.com/tonyhe_rounded_apple_touch.png"
					/>
					<link
						rel="mask-icon"
						href="https://static.ouorz.com/ouorz-mask.ico"
					/>
					<meta name="baidu-site-verification" content="codeva-4VKhm5g62Z" />
				</Head>
				<body>
					<Main />
					<NextScript />
				</body>
			</Html>
		)
	}
}

export default AppDocument
