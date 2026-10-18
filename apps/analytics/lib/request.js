import path from "path"
import requestIp from "request-ip"
import { browserName, detectOS } from "detect-browser"
import isLocalhost from "is-localhost-ip"
import maxmind from "maxmind"
import { isIP } from "net"

import {
	DESKTOP_OS,
	MOBILE_OS,
	DESKTOP_SCREEN_WIDTH,
	LAPTOP_SCREEN_WIDTH,
	MOBILE_SCREEN_WIDTH,
} from "./constants"

let lookup

export function getIpAddress(req) {
	// Custom header
	if (req.headers[process.env.CLIENT_IP_HEADER?.toLowerCase()]) {
		return req.headers[process.env.CLIENT_IP_HEADER.toLowerCase()]
	}
	// Cloudflare
	else if (req.headers["cf-connecting-ip"]) {
		return req.headers["cf-connecting-ip"]
	}

	return requestIp.getClientIp(req)
}

export function getDevice(screen, browser, os) {
	if (!screen) return

	const [width] = screen.split("x")

	if (DESKTOP_OS.includes(os)) {
		if (os === "Chrome OS" || width < DESKTOP_SCREEN_WIDTH) {
			return "laptop"
		}
		return "desktop"
	} else if (MOBILE_OS.includes(os)) {
		if (os === "Amazon OS" || width > MOBILE_SCREEN_WIDTH) {
			return "tablet"
		}
		return "mobile"
	}

	if (width >= DESKTOP_SCREEN_WIDTH) {
		return "desktop"
	} else if (width >= LAPTOP_SCREEN_WIDTH) {
		return "laptop"
	} else if (width >= MOBILE_SCREEN_WIDTH) {
		return "tablet"
	} else {
		return "mobile"
	}
}

export async function getCountry(req, ip) {
	// Cloudflare
	if (/^[A-Z]{2}$/.test(req.headers["cf-ipcountry"] || "")) {
		return req.headers["cf-ipcountry"]
	}

	// Ignore local ips
	if (typeof ip !== "string" || !isIP(ip) || (await isLocalhost(ip))) {
		return
	}

	// Database lookup
	if (!lookup) {
		lookup = await maxmind.open(
			path.resolve("./public/geo/GeoLite2-Country.mmdb")
		)
	}

	const result = lookup.get(ip)

	return result?.country?.iso_code
}

export async function getClientInfo(req, { screen }) {
	const userAgent = req.headers["user-agent"] || ""
	const ip = getIpAddress(req)
	const country = await getCountry(req, ip)
	const browser = browserName(userAgent)
	const os = detectOS(userAgent)
	const device = getDevice(screen, browser, os)

	return { userAgent, browser, os, ip, country, device }
}

export function getJsonBody(req) {
	try {
		const body = typeof req.body === "string" ? JSON.parse(req.body) : req.body
		return body && typeof body === "object" && !Array.isArray(body)
			? body
			: null
	} catch {
		return null
	}
}

export function getDateRange({ start_at, end_at }) {
	if (
		typeof start_at !== "string" ||
		typeof end_at !== "string" ||
		!start_at ||
		!end_at
	)
		return null
	const startDate = new Date(Number(start_at))
	const endDate = new Date(Number(end_at))
	return Number.isFinite(+startDate) &&
		Number.isFinite(+endDate) &&
		startDate <= endDate
		? { startDate, endDate }
		: null
}
