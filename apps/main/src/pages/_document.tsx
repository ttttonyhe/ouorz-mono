import Document, {
	Html,
	Head,
	Main,
	NextScript,
	DocumentContext,
	DocumentInitialProps,
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
			<Html lang="zh-cn" prefix="og: http://ogp.me/ns#">
				<Head>
					<link rel="icon" type="image/x-icon" href="/favicon.ico" />
					<link
						type="image/vnd.microsoft.icon"
						href="/favicon.ico"
						rel="shortcut icon"
					/>
					<meta
						name="description"
						content="living an absolutely not meaningless life with totally not unachievable goals."
					/>
					<meta
						name="keywords"
						content="Tony He, Lipeng He, 贺莉朋, 博客, 个人博客, 独立博客, 前端开发, 后端开发, 全栈开发, 区块链, 工程师, 研究学者, Researcher, Crypto, Blockchain, Tony, Developer, Blogger, Podcaster, Blog, Personal Site, WordPress, Next.js, React.js, TypeScript, JavaScript"
					/>
					<meta name="robots" content="index,follow" />
					<meta name="googlebot" content="index,follow" />
					<meta property="og:url" content="https://lipeng.ac" />
					<meta property="og:type" content="website" />
					<meta property="og:title" content="Tony He" />
					<meta
						property="og:description"
						content="Living an absolutely not meaningless life with totally not unachievable goals."
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
						content="Living an absolutely not meaningless life with totally not unachievable goals."
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
