const { withSentryConfig } = require("@sentry/nextjs")

/** @type {import('next').NextConfig} */
const NextConfigs = {
	assetPrefix: "/assets",
	poweredByHeader: false,
	productionBrowserSourceMaps: true,
	compress: true,
	transpilePackages: ["@twilight-toolkit/ui", "@twilight-toolkit/utils"],
	images: {
		minimumCacheTTL: 3600,
		formats: ["image/avif", "image/webp"],
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
	async rewrites() {
		return [
			{
				source: "/feed",
				destination: "/feed.xml",
			},
		]
	},
	async headers() {
		const cacheHeaders = [
			{ key: "Cache-Control", value: "max-age=3600" },
			{ key: "CDN-Cache-Control", value: `max-age=${3600 * 24}` },
			{ key: "Vercel-CDN-Cache-Control", value: `max-age=${3600 * 24 * 7}` },
		]
		return [
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
