import type { KbarListItem } from "~/components/Kbar"

// Actions watched by sagas
export const ACTIVATE_KBAR = "ACTIVATE_KBAR"
export const DEACTIVATE_KBAR = "DEACTIVATE_KBAR"
export const UPDATE_KBAR_TO_SEARCH = "UPDATE_KBAR_TO_SEARCH"
export const UPDATE_KBAR_SEARCH_QUERY = "UPDATE_KBAR_SEARCH_QUERY"
export const UPDATE_KBAR_TO_HOME = "UPDATE_KBAR_TO_HOME"
export const UPDATE_KBAR_LOCATION = "UPDATE_KBAR_LOCATION"
export const UPDATE_KBAR = "UPDATE_KBAR"

// Actions not watched by sagas
export const SHOW_KBAR = "SHOW_KBAR"
export const HIDE_KBAR = "HIDE_KBAR"
export const ADD_TO_KBAR_LISTS = "ADD_TO_KBAR_LISTS"
export const SET_KBAR_LIST = "SET_KBAR_LIST"
export const SET_KBAR_ANIMATION = "SET_KBAR_ANIMATION"
export const SET_KBAR_LOCATION = "SET_KBAR_LOCATION"
export const SET_KBAR_LOADING = "SET_KBAR_LOADING"
export const SET_KBAR_PLACEHOLDER = "SET_KBAR_PLACEHOLDER"

// Payload Types
export type KbarList = KbarListItem[]
export type KbarLists = {
	[listKey: string]: KbarList
}

/* Action creators */
/* Saga actions */
export const activateKbar = (homeList: KbarListItem[]) => {
	return {
		type: ACTIVATE_KBAR,
		payload: {
			homeList,
		},
	}
}

export const deactivateKbar = () => {
	return {
		type: DEACTIVATE_KBAR,
		payload: null,
	}
}

// Perform transition animation and update Kbar location
export const updateKbarLocation = (location: string[]) => {
	return {
		type: UPDATE_KBAR_LOCATION,
		payload: {
			location,
		},
	}
}

/*
 * Generic update kbar request
 * @note items will be to add to the cache if provided
 */
export const updateKbar = (params: {
	key: string
	location: string[]
	placeholder?: string
	items?: KbarListItem[]
}) => {
	return {
		type: UPDATE_KBAR,
		payload: {
			key: params.key,
			location: params.location,
			items: params.items,
			placeholder: params.placeholder,
		},
	}
}

// Update kbar to display a list of all blog posts for searching
export const updateKbarToSearch = () => {
	return {
		type: UPDATE_KBAR_TO_SEARCH,
		payload: null,
	}
}

// Update Kbar to display home list
export const updateKbarToHome = () => {
	return {
		type: UPDATE_KBAR_TO_HOME,
		payload: null,
	}
}

// Update Kbar search query (search via Algolia)
export const updateKbarSearchQuery = (query: string) => {
	return {
		type: UPDATE_KBAR_SEARCH_QUERY,
		payload: {
			query,
		},
	}
}

/* Redux actions */
/* UI actions */
export const showKbar = () => {
	return {
		type: SHOW_KBAR,
		payload: null,
	}
}

export const hideKbar = () => {
	return {
		type: HIDE_KBAR,
		payload: null,
	}
}

export const setKbarAnimation = (state: "in" | "out" | "transition" | "") => {
	return {
		type: SET_KBAR_ANIMATION,
		payload: {
			state,
		},
	}
}

/* Data actions */
export const addToKbarLists = (key: string, list: KbarListItem[]) => {
	return {
		type: ADD_TO_KBAR_LISTS,
		payload: {
			key,
			list,
		},
	}
}

export const setKbarList = (list: KbarListItem[]) => {
	return {
		type: SET_KBAR_LIST,
		payload: {
			list: list,
		},
	}
}

export const setKbarLocation = (location: string[]) => {
	return {
		type: SET_KBAR_LOCATION,
		payload: {
			location,
		},
	}
}

export const setKbarLoading = (status: boolean) => {
	return {
		type: SET_KBAR_LOADING,
		payload: {
			status,
		},
	}
}

export const setKbarPlaceholder = (placeholder: string) => {
	return {
		type: SET_KBAR_PLACEHOLDER,
		payload: {
			placeholder,
		},
	}
}
