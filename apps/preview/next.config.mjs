import createMDX from "@next/mdx"
import rehypeAutolinkHeadings from "rehype-autolink-headings"
import rehypeMathjax from "rehype-mathjax"
import rehypeSlug from "rehype-slug"
import remarkMath from "remark-math"

/** @type {import('next').NextConfig} */
const nextConfig = {
	reactStrictMode: true,
	poweredByHeader: false,
	productionBrowserSourceMaps: false,
	compress: true,
	// transpilePackages: ["next-mdx-remote"], enable this after the next nextjs release
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
		// styledComponents: true,
		removeConsole: {
			exclude: ["log", "error"],
		},
	},
	experimental: {
		// mdxRs: true,
		// turbo: {
		// 	resolveExtensions: [
		// 		".md",
		// 		".mdx",
		// 		".tsx",
		// 		".ts",
		// 		".jsx",
		// 		".js",
		// 		".mjs",
		// 		".json",
		// 	],
		// },
		// optimizePackageImports: ["package-name"],
	},
}

const withMDX = createMDX({
	options: {
		remarkPlugins: [remarkMath],
		rehypePlugins: [rehypeSlug, rehypeAutolinkHeadings, rehypeMathjax],
	},
})

export default withMDX(nextConfig)
