import { call, put, select } from "redux-saga/effects"
import { selectKbar } from "../selectors"
import {
	setKbarLoading,
	setKbarList,
	setKbarPlaceholder,
	updateKbarLocation,
	addToKbarLists,
} from "../actions"
import Fetcher from "~/lib/fetcher"
import getApi from "~/utilities/api"

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
			const searchData = yield call(
				Fetcher,
				getApi({
					searchIndexes: true,
				})
			)
			// construct post list data
			searchList = searchData.ids.map((id: number, index: number) => {
				const title = searchData.titles[index]
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
			})
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
