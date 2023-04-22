import { call, delay, put, select } from "redux-saga/effects"
import {
	setKbarLoading,
	setKbarList,
	updateKbarSearchQuery,
	addToKbarLists,
} from "../actions"
import { selectKbar } from "../selectors"

export default function* updateKbarSearchQuerySaga(
	action: ReturnType<typeof updateKbarSearchQuery>
) {
	try {
		// start loading
		yield put(setKbarLoading(true))

		// get list cache
		const { lists } = yield select(selectKbar)
		const cacheKey = `search-${action.payload.query}`
		let searchList = lists[cacheKey]

		// determine if post data is already in cache
		if (!searchList) {
			// fetch search index data
			const searchData = yield call(async (path) => {
				const res = await fetch(path, {
					method: "POST",
					headers: {
						"Content-Type": "application/json",
					},
					body: JSON.stringify({
						query: action.payload.query,
					}),
				})
				return await res.json()
			}, "/api/search")

			// construct post list data
			searchList = searchData.hits.map(
				(object: { post_id: string; post_title: string }) => {
					const id = object.post_id
					const title = object.post_title
					return {
						label: title,
						link: {
							internal: `/post/${id}`,
						},
						onClick: () => {
							window.location.href = `https://www.ouorz.com/post/${id}`
						},
						className: "w-full !justify-start !p-4",
					}
				}
			)

			// add post list to cache
			yield put(addToKbarLists(cacheKey, searchList))

			yield delay(1000)
		}

		// update current list
		yield put(setKbarList(searchList))

		// stop loading
		yield put(setKbarLoading(false))
	} catch (error) {
		console.error(error)
	}
}
