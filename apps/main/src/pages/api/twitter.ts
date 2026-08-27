import type { NextApiRequest, NextApiResponse } from "next"

import { TWITTER_API } from "~/constants/apiURLs"

type ResDataType = {
	followers: number
}

/**
 * The Twitter v2 free tier dropped follower counts, so this route answers with
 * an error. It stays wired up so the dashboard tile keeps its shape.
 */
const twitter = async (
	_req: NextApiRequest,
	res: NextApiResponse<ResDataType>
) => {
	try {
		const response = await fetch(
			`${TWITTER_API.USER_LOOKUP.USER_BY_USERNAME_METRICS}/ttttonyhe?user.fields=public_metrics`,
			{
				headers: {
					Authorization: `Bearer ${process.env.TWITTER_BEARER_TOKEN}`,
				},
			}
		)

		const data = await response.json()

		res.setHeader(
			"Cache-Control",
			"public, s-maxage=1200, stale-while-revalidate=600"
		)

		return res.status(200).json({
			followers: data.data?.public_metrics?.followers_count,
		})
	} catch {
		return res.status(500)
	}
}

export default twitter
