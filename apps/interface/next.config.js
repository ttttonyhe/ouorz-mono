/** @type {import('next').NextConfig} */
const nextConfig = {
	poweredByHeader: false,
	productionBrowserSourceMaps: false,
	compress: true,
	experimental: {
		typedRoutes: true,
	},
}

module.exports = nextConfig
