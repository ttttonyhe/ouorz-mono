import {
	NEXMENT_SUPABASE_ANON_KEY,
	NEXMENT_SUPABASE_URL,
} from "~/constants/ids"
import { apiHandler, ApiError, PUBLIC_CACHE } from "~/lib/apiHandler"

export default apiHandler(["GET", "HEAD"], async (_req, res) => {
	let response: Response
	try {
		response = await fetch(
			`${NEXMENT_SUPABASE_URL}/rest/v1/nexment_comments?select=id`,
			{
				method: "HEAD",
				redirect: "error",
				signal: AbortSignal.timeout(10_000),
				headers: { apikey: NEXMENT_SUPABASE_ANON_KEY, Prefer: "count=exact" },
			}
		)
	} catch {
		throw new ApiError(502, "Comment service unavailable")
	}
	const countText = response.headers.get("content-range")?.split("/")[1] ?? ""
	const count = Number(countText)
	if (
		!response.ok ||
		!/^\d+$/u.test(countText) ||
		!Number.isSafeInteger(count)
	) {
		throw new ApiError(502, "Comment service unavailable")
	}
	res.setHeader("Cache-Control", PUBLIC_CACHE)
	return res.status(200).json({ count })
})
