const Dotenv = require('dotenv-webpack')
const { withSentryConfig } = require('@sentry/nextjs')
const withTM = require('next-transpile-modules')([
	'@twilight-toolkit/ui',
	'@twilight-toolkit/utils',
])

const NextConfigs = {
	webpack(config) {
		config.plugins.push(new Dotenv())
		return config
	},
	poweredByHeader: false,
	productionBrowserSourceMaps: false,
	compress: true,
	swcMinify: true,
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
	experimental: {
		images: {
			allowFutureImage: true,
		},
	},
}

const SentryWebpackPluginOptions = {
	silent: true,
}

module.exports = withSentryConfig(
	withTM(NextConfigs),
	SentryWebpackPluginOptions
)
