import { type NextRequest, NextResponse } from "next/server"
import {
	renderNotFoundMarkdown,
	renderPagesMarkdown,
	renderProfileMarkdown,
} from "~/content/markdown"
import { STATIC_ROUTES } from "~/content/profile"

const KNOWN_PAGE_PATHS = new Set(STATIC_ROUTES.map((route) => route.path))

const DYNAMIC_ROUTE_PATTERN = /^\/(?:post|page|cate)\/\d+$/u

const parseQuality = (params: string[]) => {
	for (const part of params) {
		const trimmed = part.trim()
		if (trimmed.startsWith("q=")) {
			const parsed = Number(trimmed.slice(2))
			return Number.isNaN(parsed) ? 0 : parsed
		}
	}
	return 1
}

const prefersMarkdown = (acceptHeader: string | null) => {
	const normalized = acceptHeader?.toLowerCase()
	if (!normalized || !normalized.includes("text/markdown")) return false

	let markdownQuality = -1
	let htmlQuality = -1

	for (const entry of normalized.split(",")) {
		const [rawType = "", ...params] = entry.split(";")
		const mediaType = rawType.trim()
		const quality = parseQuality(params)

		if (mediaType === "text/markdown") {
			markdownQuality = Math.max(markdownQuality, quality)
		} else if (mediaType === "text/html") {
			htmlQuality = Math.max(htmlQuality, quality)
		}
	}

	return markdownQuality > 0 && markdownQuality >= htmlQuality
}

const markdownResponse = (body: string, status: number, noindex = false) => {
	const headers = new Headers({
		"Content-Type": "text/markdown; charset=utf-8",
		Vary: "Accept",
	})
	if (noindex) {
		headers.set("X-Robots-Tag", "noindex")
	} else {
		headers.set("Cache-Control", "public, max-age=3600")
	}
	return new NextResponse(body, { status, headers })
}

const proxy = (req: NextRequest): NextResponse => {
	const { pathname } = req.nextUrl

	if (pathname.startsWith("/assets/_next/")) {
		const url = req.nextUrl.clone()
		url.pathname = pathname.slice("/assets".length)
		return NextResponse.rewrite(url)
	}

	const isReadMethod = req.method === "GET" || req.method === "HEAD"
	if (isReadMethod && prefersMarkdown(req.headers.get("accept"))) {
		if (pathname === "/") {
			return markdownResponse(renderProfileMarkdown(), 200)
		}
		if (pathname === "/pages") {
			return markdownResponse(renderPagesMarkdown(), 200)
		}
		if (
			!KNOWN_PAGE_PATHS.has(pathname) &&
			!DYNAMIC_ROUTE_PATTERN.test(pathname)
		) {
			return markdownResponse(renderNotFoundMarkdown(pathname), 404, true)
		}
	}

	const response = NextResponse.next()
	response.headers.append("Vary", "Accept")
	return response
}

export const config = {
	matcher: [
		"/assets/_next/:path*",
		"/((?!api|_next|assets|feed$|.*\\.[a-zA-Z0-9]+$).*)",
	],
}

export default proxy
