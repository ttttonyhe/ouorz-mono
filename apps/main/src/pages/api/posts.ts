import type { NextApiRequest, NextApiResponse } from "next"

import { getPosts } from "~/content/posts"
import { getPostViews } from "~/content/views"
import { apiHandler } from "~/lib/apiHandler"

type ResDataType = {
	views: number
	count: number
}

const posts = async (
	_req: NextApiRequest,
	res: NextApiResponse<ResDataType>
) => {
	res.setHeader("Cache-Control", "no-store, max-age=0")

	const allPosts = getPosts()
	const views = (await getPostViews(allPosts)).reduce(
		(sum, value) => sum + value,
		0
	)

	return res.status(200).json({
		views,
		count: allPosts.length,
	})
}

export default apiHandler(["GET", "HEAD"], posts)
