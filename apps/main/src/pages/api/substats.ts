import type { NextApiRequest, NextApiResponse } from "next"
import { SUBSTATS_API } from "~/constants/apiURLs"

type ResDataType = {
	twitterFollowers: number
	sspaiFollowers: number
	zhihuFollowers: number
}

const substats = async (
	_req: NextApiRequest,
	res: NextApiResponse<ResDataType>
) => {
	const response = await fetch(SUBSTATS_API.SSPAI)

	const data = await response.json()

	res.setHeader(
		"Cache-Control",
		"public, s-maxage=1200, stale-while-revalidate=600"
	)

	return res.status(200).json({
		twitterFollowers: data.data.subsInEachSource.twitter,
		sspaiFollowers: data.data.subsInEachSource.sspai,
		zhihuFollowers: data.data.subsInEachSource.zhihu,
	})
}

export default substats
