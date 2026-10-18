import { useAuth } from "lib/middleware"
import { ok, methodNotAllowed, badRequest } from "lib/response"
import { getRealtimeData, getUserWebsites } from "lib/queries"
import { parseToken } from "lib/crypto"
import { SHARE_TOKEN_HEADER } from "lib/constants"

export default async (req, res) => {
	if (!(await useAuth(req, res))) return

	if (req.method === "GET") {
		const { start_at } = req.query

		const token = req.headers[SHARE_TOKEN_HEADER]

		if (!token) {
			return badRequest(res)
		}

		const claims = await parseToken(token, "realtime")
		const startDate = new Date(Number(start_at))
		if (
			claims?.user_id !== req.auth.user_id ||
			typeof start_at !== "string" ||
			!start_at ||
			!Number.isFinite(+startDate)
		) {
			return badRequest(res)
		}
		const websites = await getUserWebsites(req.auth.user_id)

		const data = await getRealtimeData(
			websites.map(({ website_id }) => website_id),
			startDate
		)

		return ok(res, data)
	}

	return methodNotAllowed(res)
}
