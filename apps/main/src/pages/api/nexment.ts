import type { NextRequest } from "next/server"
import { LEANCLOUD_API } from "~/constants/apiURLs"

const nexment = async (_req: NextRequest) => {
	const response = await fetch(`${LEANCLOUD_API.NEXMENT}?count=1&limit=0`, {
		headers: {
			"X-LC-Id": process.env.NEXT_PUBLIC_LC_ID,
			"X-LC-Key": process.env.NEXT_PUBLIC_LC_KEY,
		},
	})

	const data = await response.json()

	return new Response(
		JSON.stringify({
			count: data.count,
		}),
		{
			status: 200,
			headers: {
				"content-type": "application/json",
				"cache-control": "public, s-maxage=1200, stale-while-revalidate=600",
			},
		}
	)
}

export const config = {
	runtime: "edge",
}

export default nexment
