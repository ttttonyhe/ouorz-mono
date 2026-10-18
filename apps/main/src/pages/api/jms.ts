import { z } from "zod"

import {
	apiHandler,
	fetchJSON,
	PUBLIC_CACHE,
	requireEnv,
} from "~/lib/apiHandler"

const bandwidthSchema = z.object({
	monthly_bw_limit_b: z.number().nonnegative(),
	bw_counter_b: z.number().nonnegative(),
})

export default apiHandler(["GET", "HEAD"], async (_req, res) => {
	const data = await fetchJSON(bandwidthSchema, requireEnv("JMS_API_PATH"))
	res.setHeader("Cache-Control", PUBLIC_CACHE)
	return res.status(200).json({
		total: data.monthly_bw_limit_b / 10 ** 9,
		used: data.bw_counter_b / 10 ** 9,
	})
})
