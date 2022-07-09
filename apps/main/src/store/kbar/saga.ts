import { takeLatest } from 'redux-saga/effects'
import {
	activateKbarSaga,
	deactivateKbarSaga,
	updateKbarLocationSaga,
	updateKbarToSearchSaga,
	updateKbarToHomeSaga,
	updateKbarSaga,
} from './sagas'
import {
	ACTIVATE_KBAR,
	DEACTIVATE_KBAR,
	UPDATE_KBAR_LOCATION,
	UPDATE_KBAR_TO_SEARCH,
	UPDATE_KBAR_TO_HOME,
	UPDATE_KBAR,
} from './actions'

export default [
	takeLatest(ACTIVATE_KBAR, activateKbarSaga),
	takeLatest(DEACTIVATE_KBAR, deactivateKbarSaga),
	takeLatest(UPDATE_KBAR_LOCATION, updateKbarLocationSaga),
	takeLatest(UPDATE_KBAR_TO_SEARCH, updateKbarToSearchSaga),
	takeLatest(UPDATE_KBAR_TO_HOME, updateKbarToHomeSaga),
	takeLatest(UPDATE_KBAR, updateKbarSaga),
]
