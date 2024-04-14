import generalReducer from "./general/reducer"
import kbarReducer from "./kbar/reducer"
import readerReducer from "./reader/reducer"
import { combineReducers } from "@reduxjs/toolkit"

const reducer = combineReducers({
	reader: readerReducer,
	kbar: kbarReducer,
	general: generalReducer,
})

export default reducer
