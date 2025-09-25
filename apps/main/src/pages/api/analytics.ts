import type { NextApiRequest, NextApiResponse } from "next"
import { ANALYTICS_API } from "~/constants/apiURLs"

type ResDataType = {
	views: number
}

const duration = 7 * 24 * 3600 * 1000

const analytics = async (
	_req: NextApiRequest,
	res: NextApiResponse<ResDataType>
) => {
	const startAt = new Date(Date.now() - duration).getTime()
	const endAt = Date.now()

	const response = await fetch(
		`${ANALYTICS_API.STATS}?start_at=${startAt}&end_at=${endAt}`,
		{
			headers: {
				Accept: "application/json",
				Authorization: `Bearer ${process.env.ANALYTICS_TOKEN}`,
			},
		}
	).then((res) => res.json())

	const allTimeViews: number = response.pageviews?.value || 0

	res.setHeader(
		"Cache-Control",
		"public, s-maxage=1200, stale-while-revalidate=600"
	)

	return res.status(200).json({
		views: allTimeViews,
	})
}

export default analytics
