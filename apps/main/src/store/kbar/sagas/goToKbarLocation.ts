import { put } from 'redux-saga/effects'
import { goToKbarLocation, kbarLocationActionSet } from '../actions'

export default function* goToKbarLocationSaga(
	action: ReturnType<typeof goToKbarLocation>
) {
	try {
		yield put({
			type: kbarLocationActionSet[action.payload.location],
			payload: null,
		})
	} catch (error) {
		console.error(error)
	}
}
