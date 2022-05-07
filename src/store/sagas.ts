import { all } from 'redux-saga/effects'
import readerSaga from './reader/saga'

function* rootSaga() {
	yield all([...readerSaga])
}

export default rootSaga
