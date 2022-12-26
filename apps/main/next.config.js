/** @type {import('next').NextConfig} */

const { withSentryConfig } = require('@sentry/nextjs')

const NextConfigs = {
	poweredByHeader: false,
	productionBrowserSourceMaps: false,
	compress: true,
	experimental: {
		appDir: true,
	},
	transpilePackages: ['@twilight-toolkit/ui', '@twilight-toolkit/utils'],
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
	// FIXME: https://github.com/getsentry/sentry-javascript/issues/4103
	outputFileTracing: false,
	sentry: {
		hideSourceMaps: true,
	},
	// FIXME: remove these before release
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
