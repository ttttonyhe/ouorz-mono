import type { WPPost } from "~/constants/propTypes"

// Actions watched by sagas
export const SET_READER_REQUEST = "SET_READER_REQUEST"
export const HIDE_READER_REQUEST = "HIDE_READER_REQUEST"

// Actions not watched by sagas
export const SET_READER = "SET_READER"
export const SHOW_READER = "SHOW_READER"
export const HIDE_READER = "HIDE_READER"
export const SET_ANIMATION = "SET_ANIMATION"

// Action creators
export const setReaderRequest = (postData: WPPost) => {
	return {
		type: SET_READER_REQUEST,
		payload: {
			postData,
		},
	}
}

export const setReader = (postData: WPPost) => {
	return {
		type: SET_READER,
		payload: {
			postData,
		},
	}
}

export const setReaderAnimation = (state: "in" | "out" | "") => {
	return {
		type: SET_ANIMATION,
		payload: {
			state,
		},
	}
}

export const showReader = () => {
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

export const hideReader = () => {
	return {
		type: HIDE_READER,
		payload: null,
	}
}
