import { timingSafeEqual } from "node:crypto"

import { apiHandler, requireEnv } from "~/lib/apiHandler"
import { parseBody, revalidateBodySchema } from "~/lib/requestBody"

export default apiHandler(["POST"], async (req, res) => {
	const body = parseBody(revalidateBodySchema, req)
	if (!body) {
		return res.status(400).json({
			status: 400,
			revalidated: false,
			message: "Invalid request body",
		})
	}

	const expected = Buffer.from(requireEnv("REVALIDATION_REQUEST_TOKEN"))
	const received = Buffer.from(body.token)
	if (
		received.length !== expected.length ||
		!timingSafeEqual(received, expected)
	) {
		return res
			.status(401)
			.json({ status: 401, revalidated: false, message: "Invalid token" })
	}

	try {
		await res.revalidate(body.path)
		return res.status(200).json({ status: 200, revalidated: true })
	} catch {
		return res.status(500).json({
			status: 500,
			revalidated: false,
			message: "Unable to revalidate this path",
		})
	}
})

export const config = { api: { bodyParser: { sizeLimit: "4kb" } } }
