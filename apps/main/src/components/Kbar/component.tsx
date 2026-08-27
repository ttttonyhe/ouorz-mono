import { useEffect, useState } from "react"
import { useHotkeys } from "react-hotkeys-hook"

import {
	useBodyPointerEvents,
	useBodyScroll,
	useDispatch,
	useSelector,
} from "~/hooks"
import useAnalytics from "~/hooks/analytics"
import { activateKbar, deactivateKbar, updateKbar } from "~/store/kbar/actions"
import { searchLocation } from "~/store/kbar/sagas/updateKbarToSearch"
import { selectKbar } from "~/store/kbar/selectors"

import type { KbarProps } from "."
import {
	KbarContextProvider,
	type KbarInputValueChangeHandler,
} from "./context"
import KbarPanel from "./panel"

/**
 * Holds the query typed into the palette. It is mounted only while the kbar is
 * open, so closing the kbar discards the query without an extra state reset.
 */
const KbarSession = () => {
	const dispatch = useDispatch()
	const { animation, location } = useSelector(selectKbar)
	const [kbarInputValue, setInputValue] = useState("")
	const [kbarInputValueChangeHandler, setKbarInputValueChangeHandler] =
		useState<KbarInputValueChangeHandler | undefined>()

	useEffect(() => {
		if (location !== searchLocation || !kbarInputValueChangeHandler) return

		const timeout = window.setTimeout(() => {
			kbarInputValueChangeHandler(kbarInputValue)
		}, 300)

		return () => {
			window.clearTimeout(timeout)
		}
	}, [location, kbarInputValueChangeHandler, kbarInputValue])

	useHotkeys(
		"esc",
		() => {
			setInputValue("")
			setKbarInputValueChangeHandler(undefined)
			// non-home location, esc to go back to last location
			if (location.length >= 2) {
				dispatch(
					updateKbar({
						key: location.at(-2),
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

	return (
		<KbarContextProvider
			value={{
				inputValue: kbarInputValue,
				setInputValue,
				inputValueChangeHandler: kbarInputValueChangeHandler,
				setInputValueChangeHandler: setKbarInputValueChangeHandler,
			}}>
			<div
				data-cy="kbar-bg"
				className={`pointer-events-auto absolute z-40 h-screen w-full bg-gray-50/90 dark:bg-black/70 ${
					animation === "out" ? "animate-kbar-bg-out" : "animate-kbar-bg"
				}`}
				onClick={() => dispatch(deactivateKbar())}
			/>
			<KbarPanel />
		</KbarContextProvider>
	)
}

const Kbar = (props: KbarProps) => {
	const dispatch = useDispatch()
	const { visible } = useSelector(selectKbar)
	const [, setBodyPointerEvents] = useBodyPointerEvents()
	const [, setBodyScroll] = useBodyScroll()
	const { trackEvent } = useAnalytics()

	// Register keybinding that triggers/hides the kbar
	useHotkeys("ctrl+k, meta+k", (e) => {
		e.preventDefault()
		dispatch(activateKbar(props.list))
		trackEvent("activateKbar", "hotkey")
	})

	useEffect(() => {
		setBodyPointerEvents(!visible)
		setBodyScroll(!visible)

		return () => {
			setBodyPointerEvents(true)
			setBodyScroll(true)
		}
	}, [visible, setBodyPointerEvents, setBodyScroll])

	return visible && <KbarSession />
}

export default Kbar
