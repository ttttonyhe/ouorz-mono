import { z } from "zod"

import { ANALYTICS_API } from "~/constants/apiURLs"
import type { LocalPost } from "~/content/posts"
import { fetchJSON } from "~/lib/apiHandler"

const viewsSchema = z.object({
	pageviews: z.object({ value: z.number().int().nonnegative() }),
})
interface ViewCacheEntry {
	expiresAt: number
	result: Promise<number | null>
}

const cache = new Map<string, ViewCacheEntry>()

export const getPathViews = (
	path: string,
	signal = AbortSignal.timeout(10_000)
): Promise<number | null> => {
	const token = process.env.ANALYTICS_TOKEN
	if (!token) return Promise.resolve(null)
	const cached = cache.get(path)
	if (cached && cached.expiresAt > Date.now()) return cached.result

	const url = new URL(ANALYTICS_API.STATS)
	url.search = new URLSearchParams({
		start_at: "0",
		end_at: String(Date.now()),
		url: path,
	}).toString()
	const entry: ViewCacheEntry = {
		expiresAt: Date.now() + 300_000,
		result: fetchJSON(viewsSchema, url, {
			cache: "no-store",
			headers: { Authorization: `Bearer ${token}` },
			signal,
		})
			.then((data) => data.pageviews.value)
			.catch(() => {
				entry.expiresAt = Date.now() + 30_000
				return null
			}),
	}
	cache.set(path, entry)
	return entry.result
}

export const getPostViews = async (posts: LocalPost[]): Promise<number[]> => {
	const values: number[] = []
	const signal = AbortSignal.timeout(10_000)
	for (let index = 0; index < posts.length; index += 6) {
		// oxlint-disable-next-line no-await-in-loop -- Bound the number of concurrent analytics requests.
		const batch = await Promise.all(
			posts
				.slice(index, index + 6)
				.map(
					async (post) =>
						(await getPathViews(`/post/${post.id}`, signal)) ??
						post.post_metas.views
				)
		)
		values.push(...batch)
	}
	return values
}
