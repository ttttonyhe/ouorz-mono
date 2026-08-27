import Cors from "cors"
import type { NextApiRequest, NextApiResponse } from "next"

import { runMiddleware } from "~/lib/middleware"
import { parseBody, revalidateBodySchema } from "~/lib/requestBody"

type ResDataType = {
	status: number
	revalidated: boolean
	message?: string
}

const revalidate = async (
	req: NextApiRequest,
	res: NextApiResponse<ResDataType>
) => {
	// apply CORS middleware
	await runMiddleware(
		Cors({
			methods: ["POST"],
		}),
		req,
		res
	)

	const body = parseBody(revalidateBodySchema, req.body)

	if (!body || body.token !== process.env.REVALIDATION_REQUEST_TOKEN) {
		return res
			.status(401)
			.json({ status: 401, revalidated: false, message: "Invalid token" })
	}

	// execute revalidation
	try {
		await res.revalidate(body.path)
		return res.json({ status: 200, revalidated: true })
	} catch (err) {
		console.log(err)
		return res.status(500).json({
			status: 500,
			revalidated: false,
			message: "A server side error has occured, make sure path exists",
		})
	}
}

export default revalidate
