import type { NextApiRequest, NextApiResponse } from 'next'

type ResDataType = {
	views: number
	count: number
}

const posts = async (
	_req: NextApiRequest,
	res: NextApiResponse<ResDataType>
) => {
	const response = await fetch(
		'https://blog.ouorz.com/wp-json/tony/v1/poststats'
	)

	const data = await response.json()

	res.setHeader(
		'Cache-Control',
		'public, s-maxage=1200, stale-while-revalidate=600'
	)

	return res.status(200).json({
		views: data.views,
		count: data.count,
	})
}

export default posts
