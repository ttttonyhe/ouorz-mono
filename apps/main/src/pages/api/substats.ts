import { z } from "zod"

import { SUBSTATS_API } from "~/constants/apiURLs"
import { apiHandler, fetchJSON, PUBLIC_CACHE } from "~/lib/apiHandler"

const subscriberSchema = z.object({
	failed: z.literal(false),
	count: z.number().int().nonnegative(),
})

export default apiHandler(["GET", "HEAD"], async (_req, res) => {
	const data = await fetchJSON(subscriberSchema, SUBSTATS_API.SSPAI)
	res.setHeader("Cache-Control", PUBLIC_CACHE)
	return res.status(200).json({ sspaiFollowers: data.count })
})
