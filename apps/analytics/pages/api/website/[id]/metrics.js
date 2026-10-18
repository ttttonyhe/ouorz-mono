import { getDateRange } from "lib/request"
import {
	getPageviewMetrics,
	getSessionMetrics,
	getWebsiteById,
} from "lib/queries"
import { ok, methodNotAllowed, unauthorized, badRequest } from "lib/response"
import { allowQuery } from "lib/auth"
import { useCors } from "lib/middleware"

const sessionColumns = ["browser", "os", "device", "country", "language"]
const pageviewColumns = ["url", "referrer"]

function getTable(type) {
	if (type === "event") {
		return "event"
	}

	if (sessionColumns.includes(type)) {
		return "session"
	}

	return "pageview"
}

function getColumn(type) {
	if (type === "event") {
		return `concat(event_type, '\t', event_value)`
	}
	return type
}

export default async (req, res) => {
	if (!(await useCors(req, res))) return
	if (req.method === "GET") {
		if (!(await allowQuery(req))) {
			return unauthorized(res)
		}

		const {
			id,
			type,
			start_at,
			end_at,
			url,
			referrer,
			os,
			browser,
			device,
			country,
		} = req.query

		const websiteId = +id
		const range = getDateRange({ start_at, end_at })
		if (!range) return badRequest(res)
		const { startDate, endDate } = range

		if (sessionColumns.includes(type)) {
			let data = await getSessionMetrics(websiteId, startDate, endDate, type, {
				url,
				referrer,
				os,
				browser,
				device,
				country,
			})

			if (type === "language") {
				let combined = Object.create(null)

				for (let { x, y } of data) {
					x = String(x).toLowerCase().split("-")[0]

					if (!combined[x]) {
						combined[x] = { x, y }
					} else {
						combined[x].y += y
					}
				}

				data = Object.values(combined)
			}

			return ok(res, data)
		}

		if (pageviewColumns.includes(type) || type === "event") {
			let domain
			if (type === "referrer") {
				const website = await getWebsiteById(websiteId)

				if (!website) {
					return badRequest(res)
				}

				domain = website.domain
			}

			const column = getColumn(type)
			const table = getTable(type)

			const data = await getPageviewMetrics(
				websiteId,
				startDate,
				endDate,
				column,
				table,
				{
					domain,
					url: type !== "url" && table !== "event" ? url : undefined,
					referrer: type !== "referrer" ? referrer : undefined,
					os: type !== "os" ? os : undefined,
					browser: type !== "browser" ? browser : undefined,
					device: type !== "device" ? device : undefined,
					country: type !== "country" ? country : undefined,
					event_url: type !== "url" && table === "event" ? url : undefined,
				}
			)

			return ok(res, data)
		}
		return badRequest(res)
	}

	return methodNotAllowed(res)
}
