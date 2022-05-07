import { SET_READER, SET_ANIMATION, SHOW_READER, HIDE_READER } from './actions'
import type { ReaderAction } from './actions'
import { WPPost } from '~/constants/propTypes'

type InitialState = {
	animation: string
	visible: boolean
	postData?: WPPost
}

const ReaderInitialState: InitialState = {
	animation: '',
	visible: false,
	postData: null,
}

const readerReducer = (
	state = ReaderInitialState,
	action: ReaderAction
): typeof ReaderInitialState => {
	console.error(action)
	switch (action.type) {
		case SET_READER:
			return {
				...state,
				postData: action.payload.postData,
			}
		case SHOW_READER:
			return {
				...state,
				visible: true,
			}
		case HIDE_READER:
			return {
				...state,
				visible: false,
			}
		case SET_ANIMATION:
			return {
				...state,
				animation: action.payload.className,
			}
		default:
			return state
	}
}

export default readerReducer
