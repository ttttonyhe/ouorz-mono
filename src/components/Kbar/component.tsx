import React, { useEffect, useState } from 'react'
import { useHotkeys } from 'react-hotkeys-hook'
import { KbarContextProvider } from './context'
import { KbarProps } from '.'
import KbarPanel from './panel'
import { useSelector, useDispatch, useBodyPointerEvents } from '~/hooks'
import { selectKbar } from '~/store/kbar/selectors'
import {
	activateKbar,
	deactivateKbar,
	goToKbarLocation,
} from '~/store/kbar/actions'

const Kbar = (props: KbarProps) => {
	const dispatch = useDispatch()
	const { visible, animation, location } = useSelector(selectKbar)
	const [kbarInputValue, setInputValue] = useState('')
	const [_, setBodyPointerEvents] = useBodyPointerEvents()

	// register keybinding that triggers/hides the kbar
	useHotkeys('ctrl+k, command+k', (e) => {
		e.preventDefault()
		dispatch(activateKbar(props.list))
	})
	useHotkeys(
		'esc',
		() => {
			// non-home location, esc to go back to last location
			if (location.length >= 2) {
				dispatch(goToKbarLocation(location[location.length - 2]))
			} else {
				// home location, esc to hide kbar
				dispatch(deactivateKbar())
			}
		},
		{
			enableOnTags: ['INPUT'],
		}
	)

	// effects on kbar visibility change
	useEffect(() => {
		// clear input value when kbar is closed
		!visible && setInputValue('')
		// disbale body pointer events when kbar is open
		setBodyPointerEvents(!visible)

		return () => {
			setBodyPointerEvents(true)
		}
	}, [visible])

	return (
		visible && (
			<KbarContextProvider
				value={{
					inputValue: kbarInputValue,
					setInputValue,
				}}
			>
				<div
					data-cy="kbar-bg"
					className={`absolute bg-gray-50/90 dark:bg-black/70 h-screen w-full z-40 pointer-events-auto ${
						animation === 'out' ? 'animate-kbarBgOut' : 'animate-kbarBg'
					}`}
					onClick={() => dispatch(deactivateKbar())}
				/>
				<KbarPanel />
			</KbarContextProvider>
		)
	)
}

export default Kbar
