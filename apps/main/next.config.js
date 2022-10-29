/** @type {import('next').NextConfig} */

const { withSentryConfig } = require('@sentry/nextjs')

const NextConfigs = {
	poweredByHeader: false,
	productionBrowserSourceMaps: false,
	compress: true,
	// FIXME: https://github.com/getsentry/sentry-javascript/issues/4103
	outputFileTracing: false,
	images: {
		minimumCacheTTL: 3600,
		formats: ['image/avif', 'image/webp'],
		domains: ['static.ouorz.com', 'storage.snapaper.com'],
	},
	compiler: {
		styledComponents: true,
		removeConsole: {
			exclude: ['log', 'error'],
		},
	},
	sentry: {
		hideSourceMaps: true,
	},
	experimental: {
		appDir: true,
		transpilePackages: ['@twilight-toolkit/ui', '@twilight-toolkit/utils'],
	},
	typescript: {
		ignoreBuildErrors: true,
	},
	eslint: {
		ignoreDuringBuilds: true,
	},
}

const SentryWebpackPluginOptions = {
	silent: true,
}

module.exports = withSentryConfig(NextConfigs, SentryWebpackPluginOptions)
