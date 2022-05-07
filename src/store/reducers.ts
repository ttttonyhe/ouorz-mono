import { combineReducers } from '@reduxjs/toolkit'
import readerReducer from './reader/reducer'

const reducer = combineReducers({
	reader: readerReducer,
})

export default reducer
