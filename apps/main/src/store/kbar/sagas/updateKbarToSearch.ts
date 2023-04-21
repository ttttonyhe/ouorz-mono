import { call, put, select } from "redux-saga/effects"
import { selectKbar } from "../selectors"
import {
	setKbarLoading,
	setKbarList,
	setKbarPlaceholder,
	updateKbarLocation,
	addToKbarLists,
} from "../actions"

const searchLocation = ["home", "search"]

export default function* updateKbarToSearchSaga() {
	try {
		// update kbar location
		yield put(updateKbarLocation(searchLocation))
		// start loading
		yield put(setKbarLoading(true))
		// set kbar placeholder
		yield put(setKbarPlaceholder("Search articles..."))

		// get list cache
		const { lists } = yield select(selectKbar)
		let searchList = lists["search"]

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
						query: "",
					}),
				})
				return await res.json()
			}, "api/search")
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
			yield put(addToKbarLists("search", searchList))
		}

		// update current list
		yield put(setKbarList(searchList))

		// stop loading
		yield put(setKbarLoading(false))
	} catch (error) {
		console.error(error)
	}
}
