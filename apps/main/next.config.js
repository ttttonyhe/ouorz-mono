const path = require("node:path")
const { withSentryConfig } = require("@sentry/nextjs/config")

/** @type {import('next').NextConfig} */
const NextConfigs = {
	assetPrefix: "/assets",
	turbopack: {
		root: path.resolve(__dirname, "../.."),
	},
	poweredByHeader: false,
	productionBrowserSourceMaps: true,
	compress: true,
	transpilePackages: ["@twilight-toolkit/ui", "@twilight-toolkit/utils"],
	images: {
		minimumCacheTTL: 3600,
		formats: ["image/avif", "image/webp"],
		qualities: [75, 90],
		remotePatterns: [
			{
				protocol: "https",
				hostname: "static.ouorz.com",
			},
			{
				protocol: "https",
				hostname: "storage.snapaper.com",
			},
			{
				protocol: "https",
				hostname: "i.gr-assets.com",
			},
			{
				protocol: "https",
				hostname: "luneresearch.com",
				pathname: "/lune-logo.svg",
				search: "",
			},
			{
				protocol: "https",
				hostname: "retroguard.ai",
				pathname: "/logo.svg",
				search: "",
			},
			{
				protocol: "https",
				hostname: "www.snapodcast.com",
			},
		],
		path: "/assets/_next/image",
	},
	compiler: {
		styledComponents: true,
		removeConsole: {
			exclude: ["log", "error"],
		},
	},
	rewrites() {
		return [
			{
				source: "/feed",
				destination: "/feed.xml",
			},
		]
	},
	headers() {
		const isDevelopment = process.env.NODE_ENV === "development"
		// Static pages use next-themes' inline bootstrap before hydration.
		const contentSecurityPolicy = [
			"default-src 'self'",
			`script-src 'self' 'unsafe-inline' https://analytics.ouorz.com${isDevelopment ? " 'unsafe-eval'" : ""}`,
			"style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
			"img-src 'self' https: data: blob:",
			"font-src 'self' https: data:",
			`connect-src 'self' https://analytics.ouorz.com https://lists.lipeng.ac https://*.ingest.sentry.io https://*.ingest.us.sentry.io https://ainpzfmspbpvpfcatnwq.supabase.co wss://ainpzfmspbpvpfcatnwq.supabase.co${isDevelopment ? " ws: wss:" : ""}`,
			"media-src 'self' https: blob:",
			"worker-src 'self' blob:",
			"object-src 'none'",
			"frame-src 'none'",
			"frame-ancestors 'none'",
			"base-uri 'self'",
			"form-action 'self'",
			...(isDevelopment ? [] : ["upgrade-insecure-requests"]),
		].join("; ")
		const cacheHeaders = [
			{ key: "Cache-Control", value: "max-age=3600" },
			{ key: "CDN-Cache-Control", value: `max-age=${3600 * 24}` },
			{ key: "Vercel-CDN-Cache-Control", value: `max-age=${3600 * 24 * 7}` },
		]
		return [
			{
				source: "/:path*",
				headers: [
					{ key: "Content-Security-Policy", value: contentSecurityPolicy },
					{ key: "X-Content-Type-Options", value: "nosniff" },
					{ key: "X-Frame-Options", value: "DENY" },
					{ key: "Referrer-Policy", value: "strict-origin-when-cross-origin" },
					{
						key: "Permissions-Policy",
						value: "camera=(), microphone=(), geolocation=()",
					},
				],
			},
			{
				source: "/api/:path*",
				headers: [{ key: "X-Robots-Tag", value: "noindex" }],
			},
			{
				source: "/feed.xml",
				headers: [
					{ key: "Content-Type", value: "application/rss+xml; charset=utf-8" },
					...cacheHeaders,
				],
			},
			{
				source: "/sitemap.xml",
				headers: [
					{ key: "Content-Type", value: "text/xml; charset=utf-8" },
					...cacheHeaders,
				],
			},
			{
				source: "/llms.txt",
				headers: [
					{ key: "Content-Type", value: "text/plain; charset=utf-8" },
					...cacheHeaders,
				],
			},
		]
	},
}

const SentryWebpackPluginOptions = {
	silent: true,
	sourcemaps: {
		disable: true,
	},
	hideSourceMaps: true,
}

module.exports = withSentryConfig(NextConfigs, SentryWebpackPluginOptions)
