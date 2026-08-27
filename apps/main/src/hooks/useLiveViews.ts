import useSWR from "swr"

import getAPI from "~/utilities/api"

const REFRESH_INTERVAL = 15_000

const fetchFresh = (url: string) =>
	fetch(url, { cache: "no-store" }).then((response) => {
		if (!response.ok) throw new Error(`Request failed: ${response.status}`)
		return response.json()
	})

interface LiveViews {
	views: number
	isLoading: boolean
}

/**
 * Polls the current view count for a post or page, falling back to the count
 * that was rendered with the content until the first response arrives.
 */
const useLiveViews = (
	resource: "post" | "page",
	id: string | number | undefined,
	fallbackViews: number
): LiveViews => {
	const { data, isLoading } = useSWR(
		id === undefined ? null : getAPI("internal", resource, { id: Number(id) }),
		fetchFresh,
		{ refreshInterval: REFRESH_INTERVAL }
	)

	return {
		views: Number(data?.post_metas?.views ?? fallbackViews),
		isLoading,
	}
}

export default useLiveViews
