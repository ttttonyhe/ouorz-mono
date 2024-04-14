import {
	setKbarLocation,
	setKbarAnimation,
	updateKbarLocation,
} from "../actions"
import { put, delay } from "redux-saga/effects"

export default function* updateKbarLocationSaga(
	action: ReturnType<typeof updateKbarLocation>
) {
	try {
		// set transition animation
		yield put(setKbarAnimation("transition"))
		// wait for animation to finish
		yield delay(200)
		// remove animation
		yield put(setKbarAnimation(""))
		// set kbar location
		yield put(setKbarLocation(action.payload.location))
	} catch (error) {
		console.error(error)
	}
}
