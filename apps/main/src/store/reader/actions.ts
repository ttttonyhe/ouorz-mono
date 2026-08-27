import type { WPPost } from "~/constants/propTypes"

// Actions watched by sagas
export const SET_READER_REQUEST = "SET_READER_REQUEST"
export const HIDE_READER_REQUEST = "HIDE_READER_REQUEST"

// Actions not watched by sagas
export const SET_READER = "SET_READER"
export const SHOW_READER = "SHOW_READER"
export const HIDE_READER = "HIDE_READER"
export const SET_ANIMATION = "SET_ANIMATION"

/* Action shapes handled by the reducer */
export type SetReaderAction = {
	type: typeof SET_READER
	payload: { postData: WPPost }
}
export type ShowReaderAction = { type: typeof SHOW_READER; payload: null }
export type HideReaderAction = { type: typeof HIDE_READER; payload: null }
export type SetReaderAnimationAction = {
	type: typeof SET_ANIMATION
	payload: { state: "in" | "out" | "" }
}

export type ReaderAction =
	| SetReaderAction
	| ShowReaderAction
	| HideReaderAction
	| SetReaderAnimationAction

/** Every action type the reader reducer knows how to handle. */
export const READER_REDUCER_ACTION_TYPES: readonly string[] = [
	SET_READER,
	SHOW_READER,
	HIDE_READER,
	SET_ANIMATION,
]

// Action creators
export const setReaderRequest = (postData: WPPost) => {
	return {
		type: SET_READER_REQUEST,
		payload: {
			postData,
		},
	}
}

export const setReader = (postData: WPPost): SetReaderAction => {
	return {
		type: SET_READER,
		payload: {
			postData,
		},
	}
}

export const setReaderAnimation = (
	state: "in" | "out" | ""
): SetReaderAnimationAction => {
	return {
		type: SET_ANIMATION,
		payload: {
			state,
		},
	}
}

export const showReader = (): ShowReaderAction => {
	return {
		type: SHOW_READER,
		payload: null,
	}
}

export const hideReaderRequest = () => {
	return {
		type: HIDE_READER_REQUEST,
		payload: null,
	}
}

export const hideReader = (): HideReaderAction => {
	return {
		type: HIDE_READER,
		payload: null,
	}
}
