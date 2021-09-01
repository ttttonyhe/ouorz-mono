const Dotenv = require('dotenv-webpack')

module.exports = {
  webpack(config) {
    config.plugins.push(new Dotenv())
    return config
  },
  poweredByHeader: false,
  productionBrowserSourceMaps: false,
  compress: true,
  images: {
    domains: ['static.ouorz.com', 'storage.snapaper.com'],
  },
}
