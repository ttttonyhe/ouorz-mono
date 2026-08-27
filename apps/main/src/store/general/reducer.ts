import type { UnknownAction } from "@reduxjs/toolkit"

import { type GeneralAction, SET_HEADER_TITLE } from "./actions"

type GeneralState = {
	headerTitle: string
}

const GeneralInitialState: GeneralState = {
	headerTitle: "Tony He",
}

const isGeneralAction = (action: UnknownAction): action is GeneralAction =>
	action.type === SET_HEADER_TITLE

const generalReducer = (
	state = GeneralInitialState,
	action: UnknownAction
): GeneralState => {
	if (!isGeneralAction(action)) return state

	switch (action.type) {
		case SET_HEADER_TITLE:
			return {
				...state,
				headerTitle: action.payload.title,
			}
		default:
			return state
	}
}

export default generalReducer
