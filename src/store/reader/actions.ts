import { WPPost } from '~/constants/propTypes'
import {
	StringLiteral,
	ReduxActionWithPayload,
	ReduxActionWithoutPayload,
} from '~/utilities/dataTypes'

// Actions watched by sagas
export const SET_READER_REQUEST = 'SET_READER_REQUEST'
export const HIDE_READER_REQUEST = 'HIDE_READER_REQUEST'

// Actions not watched by sagas
export const SET_READER = 'SET_READER'
export const SHOW_READER = 'SHOW_READER'
export const HIDE_READER = 'HIDE_READER'
export const SET_ANIMATION = 'SET_ANIMATION'

// Action creators
export interface SetReaderRequestAction
	extends ReduxActionWithPayload<StringLiteral<typeof SET_READER_REQUEST>> {
	payload: {
		postData: WPPost
	}
}
export const setReaderRequest = (postData: WPPost): SetReaderRequestAction => {
	return {
		type: SET_READER_REQUEST,
		payload: {
			postData,
		},
	}
}

export interface SetReaderAction
	extends ReduxActionWithPayload<StringLiteral<typeof SET_READER>> {
	payload: {
		postData: WPPost
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

export interface SetReaderAnimation
	extends ReduxActionWithPayload<StringLiteral<typeof SET_ANIMATION>> {
	payload: {
		className: string
	}
}
export const setReaderAnimation = (className: string): SetReaderAnimation => {
	return {
		type: SET_ANIMATION,
		payload: {
			className,
		},
	}
}

export const showReader = (): ReduxActionWithoutPayload<
	StringLiteral<typeof SHOW_READER>
> => {
	return {
		type: SHOW_READER,
		payload: null,
	}
}

export const hideReaderRequest = (): ReduxActionWithoutPayload<
	StringLiteral<typeof HIDE_READER_REQUEST>
> => {
	return {
		type: HIDE_READER_REQUEST,
		payload: null,
	}
}

export const hideReader = (): ReduxActionWithoutPayload<
	StringLiteral<typeof HIDE_READER>
> => {
	return {
		type: HIDE_READER,
		payload: null,
	}
}

// Action types
export type ReaderAction =
	| SetReaderAction
	| SetReaderRequestAction
	| SetReaderAnimation
	| ReturnType<typeof showReader>
	| ReturnType<typeof hideReaderRequest>
	| ReturnType<typeof hideReader>
