import React, { useEffect, useState } from 'react'
import { useHotkeys } from 'react-hotkeys-hook'
import { KbarContextProvider } from './context'
import { KbarProps } from '.'
import KbarContent from './content'

const Kbar = (props: KbarProps) => {
	const [display, setDisplay] = useState(false)
	const [beforeHide, setBeforeHide] = useState(false)
	const [kbarLoading, setLoading] = useState(false)
	const [kbarInputValue, setInputValue] = useState(props.inputValue)

	/**
	 * Handle the kbar display state
	 *
	 * @param {boolean} display
	 */
	const setDisplayFunc = (display: boolean) => {
		if (display) {
			setDisplay(true)
		} else {
			setBeforeHide(true)
			setTimeout(() => {
				setDisplay(false)
				setBeforeHide(false)
			}, 200)
		}
	}

	// register keybinding that triggers/hides the kbar
	useHotkeys('ctrl+k, command+k', (e) => {
		e.preventDefault()
		setDisplayFunc(true)
	})
	useHotkeys('esc', () => setDisplayFunc(false), {
		enableOnTags: ['INPUT'],
	})

	// disbale body pointer events when kbar is open
	useEffect(() => {
		const bodyDOM = document.querySelector('body')
		bodyDOM.style.pointerEvents = display ? 'none' : 'auto'

		return () => {
			bodyDOM.style.pointerEvents = 'auto'
		}
	}, [display])

	return (
		<KbarContextProvider
			value={{
				list: props.list,
				keyBinding: props.keyBinding,
				loading: kbarLoading,
				placeholder: props.placeholder || 'What are you looking for?',
				inputValue: kbarInputValue,
				setLoading,
				setInputValue,
				setDisplay: setDisplayFunc,
			}}
		>
			{display && (
				<>
					<div
						className={`absolute bg-gray-50/90 dark:bg-black/70 h-screen w-full z-40 pointer-events-auto ${
							beforeHide ? 'animate-kbarBgOut' : 'animate-kbarBg'
						}`}
						onClick={() => setDisplayFunc(false)}
					/>
					<KbarContent beforeHide={beforeHide} />
				</>
			)}
		</KbarContextProvider>
	)
}

export default Kbar
