import { AnyAction } from '@reduxjs/toolkit'
import type { KbarListItem } from '~/components/Kbar'
import { KbarLists, SET_KBAR_PLACEHOLDER } from './actions'
import {
	SET_KBAR_CURRENT_LIST_BY_KEY,
	ADD_TO_KBAR_LISTS,
	SET_KBAR_ANIMATION,
	SET_KBAR_LOCATION,
	SET_KBAR_LOADING,
	HIDE_KBAR,
	SHOW_KBAR,
} from './actions'

type KbarState = {
	animation: 'in' | 'out' | 'transition' | ''
	visible: boolean
	lists: KbarLists
	currentList: KbarListItem[]
	location: string[]
	loading: boolean
	placeholder: string
}

const KbarInitialState: KbarState = {
	animation: '',
	visible: false,
	lists: {
		home: [],
	},
	currentList: [],
	location: ['home'],
	loading: false,
	placeholder: 'Type your command or search...',
}

const kbarReducer = (
	state = KbarInitialState,
	action: AnyAction
): typeof KbarInitialState => {
	switch (action.type) {
		case ADD_TO_KBAR_LISTS:
			return {
				...state,
				lists: {
					...state.lists,
					[action.payload.key]: action.payload.list,
				},
			}
		case SET_KBAR_CURRENT_LIST_BY_KEY:
			return {
				...state,
				currentList: state.lists[action.payload.key],
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
			return {
				...state,
				loading: action.payload.status,
			}
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
