import { PayloadAction } from '@reduxjs/toolkit'
import { ReaderState } from '../slices/reader'
import { WPPost } from '~/constants/propTypes'

/**
 * Idle the reader
 */
const idle = (state: ReaderState) => {
	state.idle = true
}

/**
 *  Toggle display of the reader
 */
const toggle = (state: ReaderState) => {
	state.idle = false
	state.visible = !state.visible
}

/**
 *  Set the post data, then toggle the reader
 */
const updatePost = (state: ReaderState, action: PayloadAction<WPPost>) => {
	state.postData = action.payload
	toggle(state)
}

export { toggle, updatePost, idle }
