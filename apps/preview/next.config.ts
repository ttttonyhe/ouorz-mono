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
	images: {
		minimumCacheTTL: 3600,
		formats: ["image/avif", "image/webp"],
		remotePatterns: [
			{
				protocol: "https",
				hostname: "static.ouorz.com",
			},
			{
				protocol: "https",
				hostname: "storage.snapaper.com",
			},
			{
				protocol: "https",
				hostname: "i.gr-assets.com",
			},
		],
		path: "/_next/image",
	},
	compiler: {
		removeConsole: {
			exclude: ["log", "error"],
		},
	},
}

export default nextConfig
