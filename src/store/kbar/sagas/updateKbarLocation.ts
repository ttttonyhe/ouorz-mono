import { put, delay } from 'redux-saga/effects'
import {
	setKbarAnimation,
	setKbarLocation,
	updateKbarLocation,
} from '../actions'

export default function* updateKbarLocationSaga(
	action: ReturnType<typeof updateKbarLocation>
) {
	try {
		// set transition animation
		yield put(setKbarAnimation('transition'))
		// wait for animation to finish
		yield delay(200)
		// remove animation
		yield put(setKbarAnimation(''))
		// set location in the store
		yield put(setKbarLocation(action.payload.location))
	} catch (error) {
		console.error(error)
	}
}
