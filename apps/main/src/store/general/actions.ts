export const SET_HEADER_TITLE = "SET_HEADER_TITLE"

export type SetHeaderTitleAction = {
	type: typeof SET_HEADER_TITLE
	payload: { title: string }
}

export type GeneralAction = SetHeaderTitleAction

export const setHeaderTitle = (title: string): SetHeaderTitleAction => {
	return {
		type: SET_HEADER_TITLE,
		payload: {
			title,
		},
	}
}
