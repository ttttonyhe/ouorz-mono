import Document, { Head, Html, Main, NextScript } from "next/document"
import { getPageLanguage, pageLanguageSchema } from "~/utilities/seo"

class AppDocument extends Document {
	override render() {
		const parsed = pageLanguageSchema.safeParse(
			this.props.__NEXT_DATA__.props.pageProps
		)
		const lang = parsed.success ? getPageLanguage(parsed.data) : "en"

		return (
			<Html lang={lang}>
				<Head>
					<link
						rel="icon"
						type="image/x-icon"
						href="/favicon.ico"
						media="(prefers-color-scheme: light)"
					/>
					<link
						rel="icon"
						type="image/x-icon"
						href="/favicon-dark.ico"
						media="(prefers-color-scheme: dark)"
					/>
					<link rel="icon" type="image/png" sizes="512x512" href="/icon.png" />
					<link rel="apple-touch-icon" href="/apple-touch-icon.png" />
					<link rel="manifest" href="/manifest.webmanifest" />
					<meta
						name="theme-color"
						content="#f6f7f8"
						media="(prefers-color-scheme: light)"
					/>
					<meta
						name="theme-color"
						content="#171717"
						media="(prefers-color-scheme: dark)"
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
