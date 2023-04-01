export const SET_HEADER_TITLE = 'SET_HEADER_TITLE'

export const setHeaderTitle = (title: string) => {
	return {
		type: SET_HEADER_TITLE,
		payload: {
			title,
		},
	}
}
