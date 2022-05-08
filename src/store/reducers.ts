import { combineReducers } from '@reduxjs/toolkit'
import readerReducer from './reader/reducer'
import kbarReducer from './kbar/reducer'

const reducer = combineReducers({
	reader: readerReducer,
	kbar: kbarReducer,
})

export default reducer
