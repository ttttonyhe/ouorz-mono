import type { NextApiRequest, NextApiResponse } from "next"

type ResDataType = {
	total: number
	used: number
}

const jms = async (_req: NextApiRequest, res: NextApiResponse<ResDataType>) => {
	const response = await fetch(process.env.JMS_API_PATH)

	const data = await response.json()

	res.setHeader(
		"Cache-Control",
		"public, s-maxage=1200, stale-while-revalidate=600"
	)

	return res.status(200).json({
		total: data.monthly_bw_limit_b / Math.pow(10, 9),
		used: data.bw_counter_b / Math.pow(10, 9),
	})
}

export default jms
