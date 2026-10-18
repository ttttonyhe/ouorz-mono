import { takeLatest } from "redux-saga/effects"

import {
	ACTIVATE_KBAR,
	DEACTIVATE_KBAR,
	UPDATE_KBAR,
	UPDATE_KBAR_LOCATION,
	UPDATE_KBAR_SEARCH_QUERY,
	UPDATE_KBAR_TO_HOME,
	UPDATE_KBAR_TO_SEARCH,
	type activateKbar,
	type deactivateKbar,
	type updateKbar,
	type updateKbarLocation,
	type updateKbarSearchQuery,
	type updateKbarToHome,
	type updateKbarToSearch,
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

type KbarRequest = ReturnType<
	| typeof activateKbar
	| typeof deactivateKbar
	| typeof updateKbar
	| typeof updateKbarToHome
	| typeof updateKbarToSearch
	| typeof updateKbarSearchQuery
>

function* handleKbarRequest(action: KbarRequest) {
	switch (action.type) {
		case ACTIVATE_KBAR:
			yield* activateKbarSaga(action)
			break
		case DEACTIVATE_KBAR:
			yield* deactivateKbarSaga()
			break
		case UPDATE_KBAR_TO_HOME:
			yield* updateKbarToHomeSaga()
			break
		case UPDATE_KBAR_TO_SEARCH:
			yield* updateKbarToSearchSaga()
			break
		case UPDATE_KBAR_SEARCH_QUERY:
			yield* updateKbarSearchQuerySaga(action)
			break
		case UPDATE_KBAR:
			yield* updateKbarSaga(action)
			break
	}
}

const kbarSagas = [
	takeLatest(
		[UPDATE_KBAR_LOCATION, ACTIVATE_KBAR, DEACTIVATE_KBAR],
		function* (
			action: ReturnType<
				typeof updateKbarLocation | typeof activateKbar | typeof deactivateKbar
			>
		) {
			if (action.type === UPDATE_KBAR_LOCATION)
				yield* updateKbarLocationSaga(action)
		}
	),
	takeLatest(
		[
			ACTIVATE_KBAR,
			DEACTIVATE_KBAR,
			UPDATE_KBAR,
			UPDATE_KBAR_TO_HOME,
			UPDATE_KBAR_TO_SEARCH,
			UPDATE_KBAR_SEARCH_QUERY,
		],
		handleKbarRequest
	),
]

export default kbarSagas
