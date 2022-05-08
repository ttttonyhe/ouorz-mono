import { put } from 'redux-saga/effects'
import {
	setKbarCurrentListByKey,
	updateKbarLocation,
	setKbarPlaceholder,
	setKbarLoading,
} from '../actions'

export default function* setKbarToHomeSaga() {
	try {
		// stop loading incase it's still running
		yield put(setKbarLoading(false))
		// set the current list to the home list
		yield put(setKbarCurrentListByKey('home'))
		// set kbar placeholder
		yield put(setKbarPlaceholder('Type your command or search...'))
		// set the location to home
		yield put(updateKbarLocation(['home']))
	} catch (error) {
		console.error(error)
	}
}
