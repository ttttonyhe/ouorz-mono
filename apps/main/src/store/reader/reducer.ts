import type { UnknownAction } from "@reduxjs/toolkit"

import type { WPPost } from "~/constants/propTypes"

import {
	HIDE_READER,
	READER_REDUCER_ACTION_TYPES,
	type ReaderAction,
	SET_ANIMATION,
	SET_READER,
	SHOW_READER,
} from "./actions"

type ReaderState = {
	animation: "in" | "out" | ""
	visible: boolean
	postData: WPPost | null
}

const ReaderInitialState: ReaderState = {
	animation: "",
	visible: false,
	postData: null,
}

const isReaderAction = (action: UnknownAction): action is ReaderAction =>
	READER_REDUCER_ACTION_TYPES.includes(action.type)

const readerReducer = (
	state = ReaderInitialState,
	action: UnknownAction
): ReaderState => {
	if (!isReaderAction(action)) return state

	switch (action.type) {
		case SET_READER:
			return {
				...state,
				postData: action.payload.postData,
			}
		case SHOW_READER:
			return {
				...state,
				visible: true,
			}
		case HIDE_READER:
			return {
				...state,
				visible: false,
			}
		case SET_ANIMATION:
			return {
				...state,
				animation: action.payload.state,
			}
		default:
			return state
	}
}

export default readerReducer
