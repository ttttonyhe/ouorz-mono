const webpack = require('webpack')

const { parsed: env } = require('dotenv').config()

module.exports = {
  future: {
    webpack5: true,
  },
  webpack(config) {
    config.plugins.push(new webpack.EnvironmentPlugin(env))
    return config
  },
  poweredByHeader: false,
  productionBrowserSourceMaps: true,
  compress: true,
  images: {
    domains: ['static.ouorz.com'],
  },
}
