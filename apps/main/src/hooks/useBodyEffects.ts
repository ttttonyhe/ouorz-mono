import { useEffect, useState, useRef, Dispatch, SetStateAction } from "react"

/**
 * Hook to turn on/off body scrolling
 */
const useBodyScroll = (): [boolean, Dispatch<SetStateAction<boolean>>] => {
	if (typeof document === "undefined") return [false, (t: boolean) => t]

	const bodyRef = useRef<HTMLElement | null>(null)
	const [scrollable, setScrollable] = useState(true)

	useEffect(() => {
		// Initialize the ref with document.body in useEffect to avoid hydration mismatch
		if (!bodyRef.current) {
			bodyRef.current = document.body
		}

		if (!bodyRef.current) return

		if (scrollable) {
			bodyRef.current.style.overflow = "auto"
		} else {
			bodyRef.current.style.overflow = "hidden"
		}
	}, [scrollable])

	return [scrollable, setScrollable]
}

/**
 * Hook to set body pointerEvents to auto/none
 */
const useBodyPointerEvents = (): [
	boolean,
	Dispatch<SetStateAction<boolean>>,
] => {
	if (typeof document === "undefined") return [false, (t: boolean) => t]

	const bodyRef = useRef<HTMLElement | null>(null)
	const [pointerEvents, setPointerEvents] = useState(true)

	useEffect(() => {
		// Initialize the ref with document.body in useEffect to avoid hydration mismatch
		if (!bodyRef.current) {
			bodyRef.current = document.body
		}

		if (!bodyRef.current) return

		if (pointerEvents) {
			bodyRef.current.style.pointerEvents = "auto"
		} else {
			bodyRef.current.style.pointerEvents = "none"
		}
	}, [pointerEvents])

	return [pointerEvents, setPointerEvents]
}

export { useBodyScroll, useBodyPointerEvents }
