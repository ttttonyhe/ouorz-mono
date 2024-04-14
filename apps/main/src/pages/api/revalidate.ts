import { use } from "../../lib/middleware"
import Cors from "cors"
import type { NextApiRequest, NextApiResponse } from "next"

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
	await use(
		Cors({
			methods: ["POST"],
		}),
		req,
		res
	)

	// deconstruct post request body
	const { token, path } = req.body

	// validate token
	if (token !== process.env.REVALIDATION_REQUEST_TOKEN) {
		return res
			.status(401)
			.json({ status: 401, revalidated: false, message: "Invalid token" })
	}

	// execute revalidation
	try {
		await res.revalidate(path)
		return res.json({ status: 401, revalidated: true })
	} catch (err) {
		return res.status(500).json({
			status: 500,
			revalidated: false,
			message: "A server side error has occured, make sure path exists",
		})
	}
}

export default revalidate
