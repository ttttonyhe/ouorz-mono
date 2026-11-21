import { fileURLToPath } from "node:url"

import type { NextConfig } from "next"

const nextConfig: NextConfig = {
	reactStrictMode: true,
	reactCompiler: true,
	poweredByHeader: false,
	productionBrowserSourceMaps: false,
	compress: true,
	typedRoutes: true,
	experimental: {
		inlineCss: true,
	},
	turbopack: {
		root: fileURLToPath(new URL("../..", import.meta.url)),
	},
}

export default nextConfig
