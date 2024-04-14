import withMDX from "@next/mdx"

/** @type {import('next').NextConfig} */
const nextConfig = {
	pageExtensions: ["ts", "tsx", "mdx", "md"],
	reactStrictMode: true,
	poweredByHeader: false,
	productionBrowserSourceMaps: false,
	compress: true,
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
		styledComponents: true,
		// removeConsole: {
		// 	exclude: ["log", "error"],
		// },
	},
	experimental: {
		mdxRs: true,
		turbo: {
			resolveExtensions: [
				".md",
				".mdx",
				".tsx",
				".ts",
				".jsx",
				".js",
				".mjs",
				".json",
			],
		},
		// optimizePackageImports: ["package-name"],
	},
}

export default withMDX()(nextConfig)
