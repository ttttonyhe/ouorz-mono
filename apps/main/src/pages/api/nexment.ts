import type { NextRequest } from "next/server"

const SUPABASE_URL = "https://ainpzfmspbpvpfcatnwq.supabase.co"
const SUPABASE_KEY = "sb_publishable_LT9i2hIpENWPlyXyj-jmSw_ra4mPiSP"

const nexment = async (_req: NextRequest) => {
	const response = await fetch(
		`${SUPABASE_URL}/rest/v1/nexment_comments?select=*`,
		{
			method: "HEAD",
			headers: {
				apikey: SUPABASE_KEY,
				Authorization: `Bearer ${SUPABASE_KEY}`,
				Prefer: "count=exact",
			},
		}
	)

	const count = parseInt(
		response.headers.get("content-range")?.split("/")[1] ?? "0",
		10
	)

	return new Response(
		JSON.stringify({
			count,
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
