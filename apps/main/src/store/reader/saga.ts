import { takeLatest } from "redux-saga/effects"
import { HIDE_READER_REQUEST, SET_READER_REQUEST } from "./actions"
import { hideReaderRequstSaga, setReaderRequestSaga } from "./sagas"

export default [
	takeLatest(SET_READER_REQUEST, setReaderRequestSaga),
	takeLatest(HIDE_READER_REQUEST, hideReaderRequstSaga),
]
