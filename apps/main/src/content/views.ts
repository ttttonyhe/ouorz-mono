import { ANALYTICS_API } from "~/constants/apiURLs"

export const getPathViews = async (path: string): Promise<number> => {
	const startAt = 0
	const endAt = Date.now()
	const apiURL = `${ANALYTICS_API.STATS}?start_at=${startAt}&end_at=${endAt}&url=${encodeURIComponent(path)}`

	try {
		const response = await fetch(apiURL, {
			cache: "no-store",
			headers: {
				Accept: "application/json",
				Authorization: `Bearer ${process.env.ANALYTICS_TOKEN}`,
			},
		})

		if (!response.ok) {
			return 0
		}

		const data = await response.json()
		return Number(data?.pageviews?.value ?? 0)
	} catch {
		return 0
	}
}
