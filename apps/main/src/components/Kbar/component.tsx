import React, { useEffect, useState } from "react"
import { useHotkeys } from "react-hotkeys-hook"
import { KbarContextProvider } from "./context"
import { KbarProps } from "."
import KbarPanel from "./panel"
import {
	useSelector,
	useDispatch,
	useBodyPointerEvents,
	useBodyScroll,
	useDebounce,
} from "~/hooks"
import { selectKbar } from "~/store/kbar/selectors"
import { activateKbar, deactivateKbar, updateKbar } from "~/store/kbar/actions"
import useAnalytics from "~/hooks/analytics"
import { searchLocation } from "~/store/kbar/sagas/updateKbarToSearch"

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
	useHotkeys("ctrl+k, command+k", (e) => {
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
			enableOnTags: ["INPUT"],
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
				}}
			>
				<div
					data-cy="kbar-bg"
					className={`absolute bg-gray-50/90 dark:bg-black/70 h-screen w-full z-40 pointer-events-auto ${
						animation === "out" ? "animate-kbarBgOut" : "animate-kbarBg"
					}`}
					onClick={() => dispatch(deactivateKbar())}
				/>
				<KbarPanel />
			</KbarContextProvider>
		)
	)
}

export default Kbar
