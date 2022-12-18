import type { NextApiRequest, NextApiResponse } from 'next'

type ResDataType = {
	views: number
}

const analytics = async (
	_req: NextApiRequest,
	res: NextApiResponse<ResDataType>
) => {
	const startAt = new Date(
		new Date().getTime() - 7 * 24 * 3600 * 1000
	).getTime()
	const endAt = new Date().getTime()

	const response = await fetch(
		`https://analytics.ouorz.com/api/website/1/stats?start_at=${startAt}&end_at=${endAt}`,
		{
			headers: {
				Accept: 'application/json',
				Authorization: `Bearer ${process.env.ANALYTICS_TOKEN}`,
			},
		}
	).then((res) => res.json())

	const allTimeViews: number = response.pageviews?.value || 0

	res.setHeader(
		'Cache-Control',
		'public, s-maxage=1200, stale-while-revalidate=600'
	)

	return res.status(200).json({
		views: allTimeViews,
	})
}

export default analytics
