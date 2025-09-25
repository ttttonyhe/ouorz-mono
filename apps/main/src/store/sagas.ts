import { all } from "redux-saga/effects"
import kbarSaga from "./kbar/saga"
import readerSaga from "./reader/saga"

function* rootSaga() {
	yield all([...readerSaga, ...kbarSaga])
}

export default rootSaga
