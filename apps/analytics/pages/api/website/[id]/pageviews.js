import { getDateRange } from "lib/request"
import moment from "moment-timezone"
import { getPageviewStats } from "lib/queries"
import { ok, badRequest, methodNotAllowed, unauthorized } from "lib/response"
import { allowQuery } from "lib/auth"
import { useCors } from "lib/middleware"

const unitTypes = ["year", "month", "hour", "day"]

export default async (req, res) => {
	if (!(await useCors(req, res))) return
	if (req.method === "GET") {
		if (!(await allowQuery(req))) {
			return unauthorized(res)
		}

		const {
			id,
			start_at,
			end_at,
			unit,
			tz,
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

		if (!moment.tz.zone(tz) || !unitTypes.includes(unit)) {
			return badRequest(res)
		}

		const [pageviews, sessions] = await Promise.all([
			getPageviewStats(websiteId, startDate, endDate, tz, unit, "*", {
				url,
				referrer,
				os,
				browser,
				device,
				country,
			}),
			getPageviewStats(
				websiteId,
				startDate,
				endDate,
				tz,
				unit,
				"distinct pageview.session_id",
				{
					url,
					referrer,
					os,
					browser,
					device,
					country,
				}
			),
		])

		return ok(res, { pageviews, sessions })
	}

	return methodNotAllowed(res)
}
