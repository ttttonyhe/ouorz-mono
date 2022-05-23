import type { KbarListItem } from '~/components/Kbar'

// Actions watched by sagas
export const ACTIVATE_KBAR = 'ACTIVATE_KBAR'
export const DEACTIVATE_KBAR = 'DEACTIVATE_KBAR'
export const SET_KBAR_TO_SEARCH = 'SET_KBAR_TO_SEARCH'
export const SET_KBAR_TO_HOME = 'SET_KBAR_TO_HOME'
export const GO_TO_KBAR_LOCATION = 'GO_TO_KBAR_LOCATION'
export const UPDATE_KBAR_LOCATION = 'UPDATE_KBAR_LOCATION'

// Actions not watched by sagas
export const SHOW_KBAR = 'SHOW_KBAR'
export const HIDE_KBAR = 'HIDE_KBAR'
export const ADD_TO_KBAR_LISTS = 'ADD_TO_KBAR_LISTS'
export const SET_KBAR_ANIMATION = 'SET_KBAR_ANIMATION'
export const SET_KBAR_LOCATION = 'SET_KBAR_LOCATION'
export const SET_KBAR_LOADING = 'SET_KBAR_LOADING'
export const SET_KBAR_PLACEHOLDER = 'SET_KBAR_PLACEHOLDER'

// Payload Types
export type KbarLists = {
	[listKey: string]: KbarListItem[]
}

// Location action set
export const kbarLocationActionSet = {
	home: SET_KBAR_TO_HOME,
	search: SET_KBAR_TO_SEARCH,
}

// Action creators
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

export const setKbarToSearch = () => {
	return {
		type: SET_KBAR_TO_SEARCH,
		payload: null,
	}
}

export const setKbarToHome = () => {
	return {
		type: SET_KBAR_TO_HOME,
		payload: null,
	}
}

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

export const goToKbarLocation = (location: string) => {
	return {
		type: GO_TO_KBAR_LOCATION,
		payload: {
			location,
		},
	}
}

export const addToKbarLists = (key: string, list: KbarListItem[]) => {
	return {
		type: ADD_TO_KBAR_LISTS,
		payload: {
			key,
			list,
		},
	}
}

export const setKbarAnimation = (state: 'in' | 'out' | 'transition' | '') => {
	return {
		type: SET_KBAR_ANIMATION,
		payload: {
			state,
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

export const updateKbarLocation = (location: string[]) => {
	return {
		type: UPDATE_KBAR_LOCATION,
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
