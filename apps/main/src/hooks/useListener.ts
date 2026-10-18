import { useEffect, useState } from "react"

type MouseEventHandler = (event: MouseEvent) => void

/**
 * Hook to add a listener for mouseLeave event
 */
export const useMouseLeaveListener = (
	eventHandler: MouseEventHandler,
	element?: HTMLElement | Document
) => {
	const type = "mouseleave"
	const handler = (event: Event) => {
		if (!(event instanceof MouseEvent)) return

		if (
			event.clientY <= 0 ||
			event.clientX <= 0 ||
			event.clientX >= window.innerWidth ||
			event.clientY >= window.innerHeight
		) {
			eventHandler(event)
		}
	}
	return useListener(type, handler, element)
}

/**
 * Hook to add a listener for a DOM event
 */
const useListener = (
	type: string,
	listener: (event: Event) => void,
	element?: HTMLElement | Document
) => {
	const [listening, setListening] = useState(true)

	useEffect(() => {
		const domElement = element || document
		domElement.addEventListener(type, listener)

		const removeListener = () => {
			domElement.removeEventListener(type, listener)
		}

		if (!listening) {
			removeListener()
		}

		return () => {
			removeListener()
		}
	}, [listening, element, listener, type])

	return [listening, setListening] as const
}

export default useListener
