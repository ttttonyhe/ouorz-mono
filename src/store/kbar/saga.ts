import { takeLatest } from 'redux-saga/effects'
import {
	activateKbarSaga,
	deactivateKbarSaga,
	setKbarToSearchSaga,
	setKbarToHomeSaga,
	updateKbarLocationSaga,
	goToKbarLocationSaga,
} from './sagas'
import {
	ACTIVATE_KBAR,
	DEACTIVATE_KBAR,
	SET_KBAR_TO_SEARCH,
	SET_KBAR_TO_HOME,
	UPDATE_KBAR_LOCATION,
	GO_TO_KBAR_LOCATION,
} from './actions'

export default [
	takeLatest(ACTIVATE_KBAR, activateKbarSaga),
	takeLatest(DEACTIVATE_KBAR, deactivateKbarSaga),
	takeLatest(SET_KBAR_TO_SEARCH, setKbarToSearchSaga),
	takeLatest(SET_KBAR_TO_HOME, setKbarToHomeSaga),
	takeLatest(UPDATE_KBAR_LOCATION, updateKbarLocationSaga),
	takeLatest(GO_TO_KBAR_LOCATION, goToKbarLocationSaga),
]
