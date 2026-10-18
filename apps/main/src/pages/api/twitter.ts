import { z } from "zod"

import { TWITTER_API } from "~/constants/apiURLs"
import {
	apiHandler,
	fetchJSON,
	PUBLIC_CACHE,
	requireEnv,
} from "~/lib/apiHandler"

const metricsSchema = z.object({
	data: z.object({
		public_metrics: z.object({
			followers_count: z.number().int().nonnegative(),
		}),
	}),
})

export default apiHandler(["GET", "HEAD"], async (_req, res) => {
	const data = await fetchJSON(
		metricsSchema,
		`${TWITTER_API.USER_LOOKUP.USER_BY_USERNAME_METRICS}/ttttonyhe?user.fields=public_metrics`,
		{
			headers: {
				Authorization: `Bearer ${requireEnv("TWITTER_BEARER_TOKEN")}`,
			},
		}
	)
	res.setHeader("Cache-Control", PUBLIC_CACHE)
	return res
		.status(200)
		.json({ followers: data.data.public_metrics.followers_count })
})
