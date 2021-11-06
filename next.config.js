const Dotenv = require('dotenv-webpack')
const { withSentryConfig } = require('@sentry/nextjs')

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
    domains: ['static.ouorz.com', 'storage.snapaper.com'],
  },
}

const SentryWebpackPluginOptions = {
  silent: true,
}

module.exports = withSentryConfig(NextConfigs, SentryWebpackPluginOptions)
