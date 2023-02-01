import type { NextApiRequest, NextApiResponse } from 'next'
import { WORDPRESS_API } from '~/constants/apiURLs'

type ResDataType = {
	views: number
	count: number
}

const posts = async (
	_req: NextApiRequest,
	res: NextApiResponse<ResDataType>
) => {
	const response = await fetch(WORDPRESS_API.POSTSTATS)

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
