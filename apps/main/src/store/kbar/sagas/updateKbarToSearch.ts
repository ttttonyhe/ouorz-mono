import { put } from "redux-saga/effects"
import {
	setKbarLoading,
	setKbarPlaceholder,
	updateKbarLocation,
} from "../actions"

export const searchLocation = ["home", "search"]

export default function* updateKbarToSearchSaga() {
	try {
		// update kbar location
		yield put(updateKbarLocation(searchLocation))

		// start loading
		yield put(setKbarLoading(true))

		// set kbar placeholder
		yield put(
			setKbarPlaceholder(
				"Search articles... (10 most revelant results are returned)"
			)
		)

		// stop loading
		yield put(setKbarLoading(false))
	} catch (error) {
		console.error(error)
	}
}
