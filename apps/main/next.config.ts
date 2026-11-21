import path from "node:path"

import { withSentryConfig } from "@sentry/nextjs/config"
import type { NextConfig } from "next"

const isDevelopment = process.env.NODE_ENV === "development"

// Static pages use next-themes' inline bootstrap before hydration.
const contentSecurityPolicy = [
	"default-src 'self'",
	`script-src 'self' 'unsafe-inline' https://analytics.ouorz.com${isDevelopment ? " 'unsafe-eval'" : ""}`,
	"style-src 'self' 'unsafe-inline'",
	"img-src 'self' https: data: blob:",
	"font-src 'self' https: data:",
	`connect-src 'self' https://analytics.ouorz.com https://lists.lipeng.ac https://*.ingest.sentry.io https://*.ingest.us.sentry.io https://ainpzfmspbpvpfcatnwq.supabase.co wss://ainpzfmspbpvpfcatnwq.supabase.co${isDevelopment ? " ws: wss:" : ""}`,
	"media-src 'self' https: blob:",
	"worker-src 'self' blob:",
	"object-src 'none'",
	"frame-src 'none'",
	"frame-ancestors 'none'",
	"base-uri 'self'",
	"form-action 'self'",
	...(isDevelopment ? [] : ["upgrade-insecure-requests"]),
].join("; ")

const cacheHeaders = [
	{ key: "Cache-Control", value: "public, max-age=3600" },
	{ key: "CDN-Cache-Control", value: `max-age=${3600 * 24}` },
	{ key: "Vercel-CDN-Cache-Control", value: `max-age=${3600 * 24 * 7}` },
]

const rssHeaders = [
	{ key: "Content-Type", value: "application/rss+xml; charset=utf-8" },
	...cacheHeaders,
]

const markdownHeaders = [
	{ key: "Content-Type", value: "text/markdown; charset=utf-8" },
	{ key: "Vary", value: "Accept" },
	{ key: "X-Robots-Tag", value: "noindex" },
	...cacheHeaders,
]

const nextConfig: NextConfig = {
	assetPrefix: "/assets",
	reactStrictMode: true,
	reactCompiler: true,
	poweredByHeader: false,
	productionBrowserSourceMaps: false,
	compress: true,
	turbopack: {
		root: path.resolve(import.meta.dirname, "../.."),
	},
	experimental: {
		optimizePackageImports: ["@twilight-toolkit/ui"],
	},
	transpilePackages: ["@twilight-toolkit/ui", "@twilight-toolkit/utils"],
	images: {
		minimumCacheTTL: 14400,
		formats: ["image/avif", "image/webp"],
		qualities: [75, 90],
		remotePatterns: [
			{
				protocol: "https",
				hostname: "static.ouorz.com",
			},
			{
				protocol: "https",
				hostname: "i.gr-assets.com",
			},
		],
		path: "/assets/_next/image",
	},
	compiler: {
		removeConsole: {
			exclude: ["log", "error"],
		},
	},
	rewrites() {
		return [
			{
				source: "/feed",
				destination: "/feed.xml",
			},
		]
	},
	headers() {
		return [
			{
				source: "/:path*",
				headers: [
					{ key: "Content-Security-Policy", value: contentSecurityPolicy },
					{ key: "X-Content-Type-Options", value: "nosniff" },
					{ key: "X-Frame-Options", value: "DENY" },
					{ key: "Referrer-Policy", value: "strict-origin-when-cross-origin" },
					{
						key: "Permissions-Policy",
						value: "camera=(), microphone=(), geolocation=()",
					},
					{
						key: "Strict-Transport-Security",
						value: "max-age=63072000",
					},
				],
			},
			{
				source: "/api/:path*",
				headers: [{ key: "X-Robots-Tag", value: "noindex" }],
			},
			{
				source: "/feed",
				headers: rssHeaders,
			},
			{
				source: "/feed.xml",
				headers: rssHeaders,
			},
			{
				source: "/sitemap.xml",
				headers: [
					{ key: "Content-Type", value: "text/xml; charset=utf-8" },
					...cacheHeaders,
				],
			},
			{
				source: "/llms.txt",
				headers: [
					{ key: "Content-Type", value: "text/plain; charset=utf-8" },
					...cacheHeaders,
				],
			},
			{
				source: "/index.md",
				headers: markdownHeaders,
			},
			{
				source: "/pages.md",
				headers: markdownHeaders,
			},
		]
	},
}

export default withSentryConfig(nextConfig, {
	silent: true,
	sourcemaps: {
		disable: true,
	},
	bundleSizeOptimizations: {
		excludeDebugStatements: true,
		excludeReplayIframe: true,
		excludeReplayShadowDom: true,
	},
})
