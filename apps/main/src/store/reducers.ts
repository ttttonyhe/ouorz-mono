import { combineReducers } from '@reduxjs/toolkit'
import readerReducer from './reader/reducer'
import kbarReducer from './kbar/reducer'
import generalReducer from './general/reducer'

const reducer = combineReducers({
	reader: readerReducer,
	kbar: kbarReducer,
	general: generalReducer,
})

export default reducer
