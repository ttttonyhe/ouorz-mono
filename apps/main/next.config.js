const { withSentryConfig } = require("@sentry/nextjs")

/** @type {import('next').NextConfig} */
const NextConfigs = {
	assetPrefix: "/assets",
	poweredByHeader: false,
	productionBrowserSourceMaps: false,
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
		],
		path: "/assets/_next/image",
	},
	compiler: {
		styledComponents: true,
		removeConsole: {
			exclude: ["log", "error"],
		},
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
