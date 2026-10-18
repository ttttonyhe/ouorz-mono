import Document, { Head, Html, Main, NextScript } from "next/document"

import { getPageLanguage, pageLanguageSchema } from "~/utilities/seo"

class AppDocument extends Document {
	render() {
		const { props } = this.props.__NEXT_DATA__
		const pageProps = pageLanguageSchema.parse(props.pageProps)
		return (
			<Html lang={getPageLanguage(pageProps)}>
				<Head>
					<link rel="icon" type="image/x-icon" href="/favicon.ico" />
					<link rel="apple-touch-icon" href="/apple-touch-icon.png" />
					<link
						type="image/x-icon"
						href="/favicon-dark.ico"
						rel="icon"
						media="(prefers-color-scheme: dark)"
					/>
					<meta name="theme-color" content="#f7f8f9" />
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
