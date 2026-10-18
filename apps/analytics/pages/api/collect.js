import isbot from "isbot"
import ipaddr from "ipaddr.js"
import { savePageView, saveEvent } from "lib/queries"
import { useCors, useSession } from "lib/middleware"
import { getJsonBody, getIpAddress } from "lib/request"
import { ok, send, badRequest, methodNotAllowed } from "lib/response"
import { createToken } from "lib/crypto"
import { removeTrailingSlash } from "lib/url"

export default async (req, res) => {
	if (!(await useCors(req, res))) return
	if (req.method !== "POST") return methodNotAllowed(res)

	const { type, payload } = getJsonBody(req) || {}
	if (!payload || typeof payload !== "object" || Array.isArray(payload))
		return badRequest(res)
	if (!["pageview", "event"].includes(type) || typeof payload.url !== "string")
		return badRequest(res)
	if (
		type === "event" &&
		[payload.event_type, payload.event_value].some(
			(value) => typeof value !== "string"
		)
	)
		return badRequest(res)
	const limits = { hostname: 100, screen: 11, language: 35, referrer: Infinity }
	if (
		Object.entries(limits).some(
			([key, limit]) =>
				payload[key] != null &&
				(typeof payload[key] !== "string" || payload[key].length > limit)
		)
	)
		return badRequest(res)

	if (isbot(req.headers["user-agent"])) {
		return ok(res)
	}

	const ignoreIps = process.env.IGNORE_IP
	if (ignoreIps) {
		const ips = ignoreIps.split(",").map((n) => n.trim())
		const ip = getIpAddress(req)
		const blocked = ips.find((i) => {
			if (i === ip) return true

			// CIDR notation
			if (i.indexOf("/") > 0 && typeof ip === "string" && ipaddr.isValid(ip)) {
				const addr = ipaddr.parse(ip)
				const range = ipaddr.parseCIDR(i)

				if (addr.kind() === range[0].kind() && addr.match(range)) return true
			}

			return false
		})

		if (blocked) {
			return ok(res)
		}
	}

	if (!(await useSession(req, res))) return

	const {
		session: { website_id, session_id },
	} = req

	let { url, referrer, event_type, event_value } = payload

	if (process.env.REMOVE_TRAILING_SLASH) {
		url = removeTrailingSlash(url)
	}

	if (type === "pageview") {
		await savePageView(website_id, session_id, url, referrer)
	} else if (type === "event") {
		await saveEvent(website_id, session_id, url, event_type, event_value)
	} else {
		return badRequest(res)
	}

	const token = await createToken(req.session, "collection")

	return send(res, token)
}
