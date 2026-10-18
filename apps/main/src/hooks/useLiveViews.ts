import useSWR from "swr"
import { z } from "zod"

import createFetcher from "~/lib/fetcher"
import api from "~/utilities/api"

const REFRESH_INTERVAL = 15_000

const fetchFresh = createFetcher(
	z.object({ post_metas: z.object({ views: z.number().int().nonnegative() }) }),
	{ cache: "no-store" }
)

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
		id === undefined ? null : api.internal[resource]({ id: Number(id) }),
		fetchFresh,
		{ refreshInterval: REFRESH_INTERVAL }
	)

	return {
		views: data?.post_metas.views ?? fallbackViews,
		isLoading,
	}
}

export default useLiveViews
