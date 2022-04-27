import { useEffect, useState, useRef, Dispatch, SetStateAction } from 'react'

export const useBodyScroll = (): [
	boolean,
	Dispatch<SetStateAction<boolean>>
] => {
	if (typeof document === 'undefined') return [false, (t: boolean) => t]

	const bodyRef = useRef<HTMLElement>(document.body)
	const [scrollable, setScrollable] = useState(true)

	useEffect(() => {
		if (!bodyRef || !bodyRef.current) return
		if (scrollable) {
			bodyRef.current.style.overflow = 'auto'
		} else {
			bodyRef.current.style.overflow = 'hidden'
		}
	}, [scrollable])

	return [scrollable, setScrollable]
}

export const useBodyPointerEvents = (): [
	boolean,
	Dispatch<SetStateAction<boolean>>
] => {
	if (typeof document === 'undefined') return [false, (t: boolean) => t]

	const bodyRef = useRef<HTMLElement>(document.body)
	const [pointerEvents, setPointerEvents] = useState(true)

	useEffect(() => {
		if (pointerEvents) {
			bodyRef.current.style.pointerEvents = 'auto'
		} else {
			bodyRef.current.style.pointerEvents = 'none'
		}
	}, [pointerEvents])

	return [pointerEvents, setPointerEvents]
}
