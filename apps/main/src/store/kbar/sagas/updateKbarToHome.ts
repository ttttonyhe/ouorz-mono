import { put } from "redux-saga/effects"

import {
	setKbarList,
	setKbarLoading,
	setKbarPlaceholder,
	updateKbarLocation,
} from "../actions"
import { selectKbarState } from "../selectors"

export default function* updateKbarToHomeSaga() {
	try {
		// update kbar location
		yield put(updateKbarLocation(["home"]))
		// set kbar placeholder
		yield put(setKbarPlaceholder("Type your command or search..."))

		// get list cache
		const { lists } = yield* selectKbarState()
		// set the location to home
		yield put(setKbarList(lists.home))

		// stop loading in case it's still running
		yield put(setKbarLoading(false))
	} catch (error) {
		console.error(error)
	}
}
