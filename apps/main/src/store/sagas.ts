import kbarSaga from "./kbar/saga"
import readerSaga from "./reader/saga"
import { all } from "redux-saga/effects"

function* rootSaga() {
	yield all([...readerSaga, ...kbarSaga])
}

export default rootSaga
