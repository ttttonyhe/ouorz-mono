import { all } from "redux-saga/effects"
import readerSaga from "./reader/saga"
import kbarSaga from "./kbar/saga"

function* rootSaga() {
	yield all([...readerSaga, ...kbarSaga])
}

export default rootSaga
