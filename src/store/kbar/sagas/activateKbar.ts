import { put } from 'redux-saga/effects'
import {
	activateKbar,
	addToKbarLists,
	setKbarAnimation,
	showKbar,
	setKbarCurrentListByKey,
} from '../actions'

export default function* activateKbarSaga(
	action: ReturnType<typeof activateKbar>
) {
	try {
		if (action.payload.homeList) {
			// set kbar list data in the store
			yield put(addToKbarLists('home', action.payload.homeList))
			// set the current list to the home list
			yield put(setKbarCurrentListByKey('home'))
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
