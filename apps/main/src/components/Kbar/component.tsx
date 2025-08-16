import { KbarProps } from "."
import { KbarContextProvider } from "./context"
import KbarPanel from "./panel"
import React, { useEffect, useState } from "react"
import { useHotkeys } from "react-hotkeys-hook"
import {
	useSelector,
	useDispatch,
	useBodyPointerEvents,
	useBodyScroll,
	useDebounce,
} from "~/hooks"
import useAnalytics from "~/hooks/analytics"
import { activateKbar, deactivateKbar, updateKbar } from "~/store/kbar/actions"
import { searchLocation } from "~/store/kbar/sagas/updateKbarToSearch"
import { selectKbar } from "~/store/kbar/selectors"

const Kbar = (props: KbarProps) => {
	const dispatch = useDispatch()
	const { visible, animation, location } = useSelector(selectKbar)
	const [kbarInputValue, setInputValue] = useState("")
	const [kbarInputValueChangeHandler, setKbarInputValueChangeHandler] =
		useState(undefined)
	const [, setBodyPointerEvents] = useBodyPointerEvents()
	const [, setBodyScroll] = useBodyScroll()
	const { trackEvent } = useAnalytics()

	// Register keybinding that triggers/hides the kbar
	useHotkeys("ctrl+k, meta+k", (e) => {
		e.preventDefault()
		dispatch(activateKbar(props.list))
		trackEvent("activateKbar", "hotkey")
	})
	useHotkeys(
		"esc",
		() => {
			setInputValue("")
			setKbarInputValueChangeHandler(undefined)
			// non-home location, esc to go back to last location
			if (location.length >= 2) {
				dispatch(
					updateKbar({
						key: location[location.length - 2],
						location: location.slice(0, location.length - 1),
					})
				)
			} else {
				// home location, esc to hide kbar
				dispatch(deactivateKbar())
			}
		},
		{
			enableOnFormTags: ["INPUT"],
		}
	)

	// Visibility effects
	useEffect(() => {
		// clear input value when kbar is closed
		!visible && setInputValue("")

		// disbale scrolling and pointer events when kbar is open
		setBodyPointerEvents(!visible)
		setBodyScroll(!visible)

		return () => {
			setBodyPointerEvents(true)
			setBodyScroll(true)
		}
	}, [visible])

	// Input effects
	useDebounce(
		() => {
			if (location === searchLocation && kbarInputValueChangeHandler) {
				kbarInputValueChangeHandler(kbarInputValue)
			}
		},
		300,
		[location, kbarInputValueChangeHandler, kbarInputValue]
	)

	return (
		visible && (
			<KbarContextProvider
				value={{
					inputValue: kbarInputValue,
					setInputValue,
					inputValueChangeHandler: kbarInputValueChangeHandler,
					setInputValueChangeHandler: setKbarInputValueChangeHandler,
				}}>
				<div
					data-cy="kbar-bg"
					className={`bg-gray-50/90 pointer-events-auto absolute z-40 h-screen w-full dark:bg-black/70 ${
						animation === "out" ? "animate-kbar-bg-out" : "animate-kbar-bg"
					}`}
					onClick={() => dispatch(deactivateKbar())}
				/>
				<KbarPanel />
			</KbarContextProvider>
		)
	)
}

export default Kbar
