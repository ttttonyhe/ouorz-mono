import { z } from "zod"

import { ANALYTICS_API } from "~/constants/apiURLs"
import {
	apiHandler,
	fetchJSON,
	PUBLIC_CACHE,
	requireEnv,
} from "~/lib/apiHandler"

const analyticsSchema = z.object({
	pageviews: z.object({ value: z.number().int().nonnegative() }),
})

export default apiHandler(["GET", "HEAD"], async (_req, res) => {
	const endAt = Date.now()
	const startAt = endAt - 7 * 24 * 3600 * 1000
	const data = await fetchJSON(
		analyticsSchema,
		`${ANALYTICS_API.STATS}?start_at=${startAt}&end_at=${endAt}`,
		{ headers: { Authorization: `Bearer ${requireEnv("ANALYTICS_TOKEN")}` } }
	)
	res.setHeader("Cache-Control", PUBLIC_CACHE)
	return res.status(200).json({ views: data.pageviews.value })
})
