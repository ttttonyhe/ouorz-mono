import { AnyAction } from '@reduxjs/toolkit'
import { KbarList, KbarLists } from './actions'
import {
	ADD_TO_KBAR_LISTS,
	SET_KBAR_LIST,
	SET_KBAR_PLACEHOLDER,
	SET_KBAR_ANIMATION,
	SET_KBAR_LOCATION,
	SET_KBAR_LOADING,
	HIDE_KBAR,
	SHOW_KBAR,
} from './actions'

type KbarState = {
	// Kbar panel animation
	animation: 'in' | 'out' | 'transition' | ''
	// Kbar visibility
	visible: boolean
	// Current displaying list
	list: KbarList
	// Kbar list cache (key-list pairs)
	lists: KbarLists
	// Kbar location (an array of list keys)
	location: string[]
	// Kbar loading status
	loading: boolean
	// Kbar input placeholder
	placeholder: string
}

const KbarInitialState: KbarState = {
	animation: '',
	visible: false,
	list: [],
	lists: {
		home: [],
	},
	location: ['home'],
	loading: false,
	placeholder: 'Type your command or search...',
}

const kbarReducer = (
	state = KbarInitialState,
	action: AnyAction
): typeof KbarInitialState => {
	switch (action.type) {
		case SET_KBAR_LIST:
			return {
				...state,
				list: action.payload.list,
			}
		case ADD_TO_KBAR_LISTS:
			return {
				...state,
				lists: {
					...state.lists,
					[action.payload.key]: action.payload.list,
				},
			}
		case SHOW_KBAR:
			return {
				...state,
				visible: true,
			}
		case HIDE_KBAR:
			return {
				...state,
				visible: false,
			}
		case SET_KBAR_ANIMATION:
			return {
				...state,
				animation: action.payload.state,
			}
		case SET_KBAR_LOCATION:
			return {
				...state,
				location: action.payload.location,
			}
		case SET_KBAR_LOADING:
			let newState = {
				...state,
				loading: action.payload.status,
			}
			if (action.payload.status) {
				newState.list = null
			}
			return newState
		case SET_KBAR_PLACEHOLDER:
			return {
				...state,
				placeholder: action.payload.placeholder,
			}
		default:
			return state
	}
}

export default kbarReducer
