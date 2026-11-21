import { useEffect, useState } from "react"
import { useHotkeys } from "react-hotkeys-hook"
import { useBodyPointerEvents, useBodyScroll } from "~/hooks"
import useAnalytics from "~/hooks/analytics"
import {
	activateKbar,
	deactivateKbar,
	SEARCH_LOCATION,
	updateKbarView,
	useAppState,
} from "~/store"

import type { KbarProps } from "."
import { KbarContext, type KbarInputValueChangeHandler } from "./context"
import KbarPanel from "./panel"

// Mounted only while open so closing resets the query automatically.
const KbarSession = () => {
	const animation = useAppState((s) => s.kbar.animation)
	const location = useAppState((s) => s.kbar.location)
	const [kbarInputValue, setInputValue] = useState("")
	const [kbarInputValueChangeHandler, setKbarInputValueChangeHandler] =
		useState<KbarInputValueChangeHandler | undefined>()

	useEffect(() => {
		if (
			location.at(-1) !== SEARCH_LOCATION.at(-1) ||
			!kbarInputValueChangeHandler
		) {
			return
		}

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
			const previousLocation = location.at(-2)
			if (previousLocation !== undefined) {
				updateKbarView({
					key: previousLocation,
					location: location.slice(0, location.length - 1),
				})
			} else {
				deactivateKbar()
			}
		},
		{ enableOnFormTags: ["INPUT"] },
		[location]
	)

	return (
		<KbarContext
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
				onClick={() => deactivateKbar()}
			/>
			<KbarPanel />
		</KbarContext>
	)
}

const Kbar = (props: KbarProps) => {
	const visible = useAppState((s) => s.kbar.visible)
	const [, setBodyPointerEvents] = useBodyPointerEvents()
	const [, setBodyScroll] = useBodyScroll()
	const { trackEvent } = useAnalytics()

	useHotkeys(
		"ctrl+k, meta+k",
		(e) => {
			e.preventDefault()
			if (document.querySelector("dialog:modal")) return

			if (visible) {
				deactivateKbar()
			} else {
				activateKbar(props.list)
				trackEvent("activateKbar", "hotkey")
			}
		},
		{ enableOnFormTags: ["INPUT", "TEXTAREA"] },
		[props.list, trackEvent, visible]
	)

	useEffect(() => {
		setBodyPointerEvents(!visible)
		setBodyScroll(!visible)
	}, [visible, setBodyPointerEvents, setBodyScroll])

	return visible ? <KbarSession /> : null
}

export default Kbar
