import {
	addToKbarLists,
	setKbarList,
	updateKbarLocation,
	setKbarPlaceholder,
	updateKbar,
	updateKbarToHome,
	updateKbarToSearch,
} from "../actions"
import { selectKbar } from "../selectors"
import { put, select } from "redux-saga/effects"

export default function* updateKbarSaga(action: ReturnType<typeof updateKbar>) {
	try {
		// delegate special update requests to their own saga
		switch (action.payload.key) {
			case "home":
				yield put(updateKbarToHome())
				return
			case "search":
				yield put(updateKbarToSearch())
				return
		}

		// update kbar location
		yield put(updateKbarLocation(action.payload.location))

		// update placeholder if needed
		if (action.payload.placeholder) {
			yield put(setKbarPlaceholder(action.payload.placeholder))
		}

		// create a new list cache if needed
		if (action.payload.items) {
			yield put(addToKbarLists(action.payload.key, action.payload.items))
		}

		// retreive list from cache
		const { lists } = yield select(selectKbar)
		// update current displaying list
		yield put(setKbarList(lists[action.payload.key]))
	} catch (error) {
		console.error(error)
	}
}
