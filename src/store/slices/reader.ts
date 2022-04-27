import { createSlice } from '@reduxjs/toolkit'
import { WPPost } from '~/constants/propTypes'
import * as readerReducers from '../reducers/reader'

export interface ReaderState {
	idle: boolean
	visible: boolean
	postData?: WPPost
}

const initialState: ReaderState = {
	idle: true,
	visible: false,
	postData: null,
}

const readerSlice = createSlice({
	name: 'reader',
	initialState,
	reducers: readerReducers,
})

export const readerActions = readerSlice.actions
export default readerSlice.reducer
