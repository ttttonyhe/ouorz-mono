import { cancel, fork, take, takeLatest } from "redux-saga/effects"
import {
	ACTIVATE_KBAR,
	DEACTIVATE_KBAR,
	UPDATE_KBAR,
	UPDATE_KBAR_LOCATION,
	UPDATE_KBAR_SEARCH_QUERY,
	UPDATE_KBAR_TO_HOME,
	UPDATE_KBAR_TO_SEARCH,
} from "./actions"
import {
	activateKbarSaga,
	deactivateKbarSaga,
	updateKbarLocationSaga,
	updateKbarSaga,
	updateKbarToHomeSaga,
	updateKbarToSearchSaga,
} from "./sagas"
import updateKbarSearchQuerySaga from "./sagas/updateKbarSearchQuery"

// Custom takeLatest function to:
// 1. Delegate update kbar requests to their own saga
// 2. Cancel previous update kbar tasks if it is still running
const takeLatestUpdateAction = (pattern: string | string[]) => {
	return fork(function* () {
		let lastTask: any

		while (true) {
			const action = yield take(pattern)
			if (lastTask) yield cancel(lastTask)

			let saga: any
			switch (action.type) {
				case UPDATE_KBAR_TO_HOME:
					saga = updateKbarToHomeSaga
					break
				case UPDATE_KBAR_TO_SEARCH:
					saga = updateKbarToSearchSaga
					break
				case UPDATE_KBAR_SEARCH_QUERY:
					saga = updateKbarSearchQuerySaga
					break
				default:
					saga = updateKbarSaga
			}

			lastTask = yield fork(saga, action)
		}
	})
}

export default [
	takeLatest(ACTIVATE_KBAR, activateKbarSaga),
	takeLatest(DEACTIVATE_KBAR, deactivateKbarSaga),
	takeLatest(UPDATE_KBAR_LOCATION, updateKbarLocationSaga),
	takeLatestUpdateAction([
		UPDATE_KBAR,
		UPDATE_KBAR_TO_HOME,
		UPDATE_KBAR_TO_SEARCH,
		UPDATE_KBAR_SEARCH_QUERY,
	]),
]
