import { takeLatest } from 'redux-saga/effects'
import { setReaderRequestSaga, hideReaderRequstSaga } from './sagas'
import { SET_READER_REQUEST, HIDE_READER_REQUEST } from './actions'

export default [
	takeLatest(SET_READER_REQUEST, setReaderRequestSaga),
	takeLatest(HIDE_READER_REQUEST, hideReaderRequstSaga),
]
