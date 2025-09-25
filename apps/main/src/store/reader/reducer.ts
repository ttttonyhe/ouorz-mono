import type { AnyAction } from "@reduxjs/toolkit"
import type { WPPost } from "~/constants/propTypes"
import { HIDE_READER, SET_ANIMATION, SET_READER, SHOW_READER } from "./actions"

type ReaderState = {
	animation: "in" | "out" | ""
	visible: boolean
	postData?: WPPost
}

const ReaderInitialState: ReaderState = {
	animation: "",
	visible: false,
	postData: null,
}

const readerReducer = (
	state = ReaderInitialState,
	action: AnyAction
): typeof ReaderInitialState => {
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
