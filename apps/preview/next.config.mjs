import path from "node:path"

import createMDX from "@next/mdx"

const workspaceRoot = path.resolve(import.meta.dirname, "../..")

/** @type {import('next').NextConfig} */
const nextConfig = {
	reactStrictMode: true,
	poweredByHeader: false,
	productionBrowserSourceMaps: false,
	compress: true,
	turbopack: {
		root: workspaceRoot,
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

const withMDX = createMDX({
	options: {
		remarkPlugins: [["remark-math", {}]],
		rehypePlugins: [
			["rehype-slug", {}],
			["rehype-autolink-headings", {}],
			["rehype-mathjax", {}],
		],
	},
})

export default withMDX(nextConfig)
