import { setReaderAnimation, hideReader } from "../actions"
import { put, delay } from "redux-saga/effects"

export default function* hideReaderRequstSaga() {
	try {
		// set animation
		yield put(setReaderAnimation("out"))
		// wait for animation to finish
		yield delay(500)
		// hide the reader
		yield put(hideReader())
	} catch (error) {
		console.error(error)
	}
}
