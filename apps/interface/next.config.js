const path = require("node:path")

/** @type {import('next').NextConfig} */
const nextConfig = {
	poweredByHeader: false,
	productionBrowserSourceMaps: false,
	compress: true,
	typedRoutes: true,
	turbopack: {
		root: path.resolve(__dirname, "../.."),
	},
}

module.exports = nextConfig
