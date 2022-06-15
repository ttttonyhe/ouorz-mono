import { put } from 'redux-saga/effects'
import {
	activateKbar,
	addToKbarLists,
	setKbarAnimation,
	setKbarLocation,
	showKbar,
	setKbarLoading
} from '../actions'

export default function* activateKbarSaga(
	action: ReturnType<typeof activateKbar>
) {
	try {
		if (action.payload.homeList) {
			// put kbar in loading state before showing it
			yield put(setKbarLoading(true))
			// set kbar list data in the store
			yield put(addToKbarLists('home', action.payload.homeList))
			// set kbar location in the store
			yield put(setKbarLocation(['home']))
			// set animation
			yield put(setKbarAnimation('in'))
			// show the reader
			yield put(showKbar())
			// stop loading
			yield put(setKbarLoading(false))
		} else {
			throw new Error('No list data provided')
		}
	} catch (error) {
		console.error(error)
	}
}
