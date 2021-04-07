const Dotenv = require('dotenv-webpack')

module.exports = {
  future: {
    webpack5: true,
  },
  webpack(config) {
    config.plugins.push(new Dotenv())
    return config
  },
  poweredByHeader: false,
  productionBrowserSourceMaps: true,
  compress: true,
  images: {
    domains: ['static.ouorz.com'],
  },
}
