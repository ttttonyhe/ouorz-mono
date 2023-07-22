/** @type {import('next').NextConfig} */

const { withSentryConfig } = require("@sentry/nextjs")

const NextConfigs = {
	assetPrefix: "/assets",
	poweredByHeader: false,
	productionBrowserSourceMaps: false,
	compress: true,
	transpilePackages: ["@twilight-toolkit/ui", "@twilight-toolkit/utils"],
	images: {
		minimumCacheTTL: 3600,
		formats: ["image/avif", "image/webp"],
		domains: ["static.ouorz.com", "storage.snapaper.com", "i.gr-assets.com"],
		path: "/assets/_next/image",
	},
	compiler: {
		styledComponents: true,
		removeConsole: {
			exclude: ["log", "error"],
		},
	},
	sentry: {
		hideSourceMaps: true,
	},
}

const SentryWebpackPluginOptions = {
	silent: true,
}

module.exports = withSentryConfig(NextConfigs, SentryWebpackPluginOptions)
