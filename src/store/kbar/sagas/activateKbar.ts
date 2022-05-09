import { put } from 'redux-saga/effects'
import {
	activateKbar,
	addToKbarLists,
	setKbarAnimation,
	setKbarLocation,
	showKbar,
} from '../actions'

export default function* activateKbarSaga(
	action: ReturnType<typeof activateKbar>
) {
	try {
		if (action.payload.homeList) {
			// set kbar list data in the store
			yield put(addToKbarLists('home', action.payload.homeList))
			console.error(action.payload.homeList)
			// set kbar location in the store
			yield put(setKbarLocation(['home']))
			// set animation
			yield put(setKbarAnimation('in'))
			// show the reader
			yield put(showKbar())
		} else {
			throw new Error('No list data provided')
		}
	} catch (error) {
		console.error(error)
	}
}
