import {
	activateKbar,
	addToKbarLists,
	setKbarAnimation,
	setKbarLocation,
	showKbar,
	setKbarLoading,
	setKbarList,
	setKbarPlaceholder,
} from "../actions"
import { put } from "redux-saga/effects"

export default function* activateKbarSaga(
	action: ReturnType<typeof activateKbar>
) {
	try {
		if (action.payload.homeList) {
			// put kbar in loading state before showing it
			yield put(setKbarLoading(true))

			// add home list to cache
			yield put(addToKbarLists("home", action.payload.homeList))
			// set current display list
			yield put(setKbarList(action.payload.homeList))
			// set kbar placeholder
			yield put(setKbarPlaceholder("Type your command or search..."))
			// set kbar location in the store
			yield put(setKbarLocation(["home"]))

			// set animation
			yield put(setKbarAnimation("in"))
			// stop loading
			yield put(setKbarLoading(false))

			// show the kbar
			yield put(showKbar())
		} else {
			throw new Error("No list data provided")
		}
	} catch (error) {
		console.error(error)
	}
}
