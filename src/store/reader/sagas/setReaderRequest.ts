import { put } from 'redux-saga/effects'
import {
	setReaderAnimation,
	SetReaderRequestAction,
	showReader,
} from '../actions'
import { setReader } from '../actions'

export default function* setReaderRequstSaga(action: SetReaderRequestAction) {
	try {
		if (action.payload.postData) {
			// update post data in the store
			yield put(setReader(action.payload.postData))
			// set animation
			yield put(setReaderAnimation('in'))
			// show the reader
			yield put(showReader())
		} else {
			throw new Error('No post data provided')
		}
	} catch (error) {
		console.log(error)
	}
}
