import { getDateRange } from "lib/request"
import moment from "moment-timezone"
import { getEventMetrics } from "lib/queries"
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

		const { id, start_at, end_at, unit, tz, url, event_type } = req.query

		if (!moment.tz.zone(tz) || !unitTypes.includes(unit)) {
			return badRequest(res)
		}

		const websiteId = +id
		const range = getDateRange({ start_at, end_at })
		if (!range) return badRequest(res)
		const { startDate, endDate } = range

		const events = await getEventMetrics(
			websiteId,
			startDate,
			endDate,
			tz,
			unit,
			{
				url,
				event_type,
			}
		)

		return ok(res, events)
	}

	return methodNotAllowed(res)
}
