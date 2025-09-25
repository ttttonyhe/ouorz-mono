import { combineReducers } from "@reduxjs/toolkit"
import generalReducer from "./general/reducer"
import kbarReducer from "./kbar/reducer"
import readerReducer from "./reader/reducer"

const reducer = combineReducers({
	reader: readerReducer,
	kbar: kbarReducer,
	general: generalReducer,
})

export default reducer
