import type { NextApiRequest, NextApiResponse } from "next"
import { LEANCLOUD_API } from "~/constants/apiURLs"

type ResDataType = {
	count: number
}

const nexment = async (
	_req: NextApiRequest,
	res: NextApiResponse<ResDataType>
) => {
	const response = await fetch(`${LEANCLOUD_API.NEXMENT}?count=1&limit=0`, {
		headers: {
			"X-LC-Id": process.env.NEXT_PUBLIC_LC_ID,
			"X-LC-Key": process.env.NEXT_PUBLIC_LC_KEY,
		},
	})

	const data = await response.json()

	res.setHeader(
		"Cache-Control",
		"public, s-maxage=1200, stale-while-revalidate=600"
	)

	return res.status(200).json({
		count: data.count,
	})
}

export default nexment
