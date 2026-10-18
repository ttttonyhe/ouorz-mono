import { call, delay, put, type CallEffect } from "redux-saga/effects"
import { z } from "zod"

import {
	addToKbarLists,
	setKbarList,
	setKbarLoading,
	type KbarList,
	type updateKbarSearchQuery,
} from "../actions"
import { selectKbarState } from "../selectors"

const searchResponseSchema = z.object({
	hits: z.array(
		z.object({ post_id: z.number().int().positive(), post_title: z.string() })
	),
})

type SearchResponse = z.infer<typeof searchResponseSchema>

const searchPosts = async (query: string, signal: AbortSignal) => {
	const response = await fetch("/api/search", {
		method: "POST",
		headers: { "Content-Type": "application/json" },
		body: JSON.stringify({ query }),
		signal,
	})
	if (!response.ok) throw new Error(`Search failed: ${response.status}`)
	return searchResponseSchema.parse(await response.json())
}

function* fetchSearchPosts(
	query: string,
	signal: AbortSignal
): Generator<CallEffect<SearchResponse>, SearchResponse, SearchResponse> {
	return yield call(searchPosts, query, signal)
}

export default function* updateKbarSearchQuerySaga(
	action: ReturnType<typeof updateKbarSearchQuery>
) {
	const controller = new AbortController()
	try {
		// start loading
		yield put(setKbarLoading(true))

		// get list cache
		const { lists } = yield* selectKbarState()
		const cacheKey = `search-${action.payload.query}`
		let searchList: KbarList = lists[cacheKey]

		// determine if post data is already in cache
		if (!searchList) {
			// fetch search index data
			const searchData = yield* fetchSearchPosts(
				action.payload.query,
				controller.signal
			)

			// construct post list data
			const totalResultLength = searchData.hits.length
			searchList = searchData.hits.map((object, index) => {
				const id = object.post_id
				const title = object.post_title
				return {
					id: `post-${id}`,
					label: title,
					link: {
						internal: `/post/${id}`,
					},
					className: "w-full justify-start! p-4!",
					description: `${index + 1} / ${totalResultLength}`,
				}
			})

			// add post list to cache
			yield put(addToKbarLists(cacheKey, searchList))

			yield delay(500)
		} else {
			yield delay(100)
		}

		// update current list
		yield put(setKbarList(searchList))
	} catch (error) {
		console.error(error)
		yield put(setKbarList([]))
	} finally {
		controller.abort()
	}

	yield put(setKbarLoading(false))
}
