import type React from "react"
import { useEffect, useSyncExternalStore } from "react"

interface Props {
	handler: (position: number) => void
	startPosition?: number
	endPosition: number
	children: React.ReactNode
}

const subscribeToScroll = (onScroll: () => void) => {
	window.addEventListener("scroll", onScroll, { passive: true })
	return () => {
		window.removeEventListener("scroll", onScroll)
	}
}

const getScrollY = () => window.scrollY
const getServerScrollY = () => 0

const ScrollWrapper = (props: Props) => {
	const {
		handler: applyEffect,
		startPosition = 0,
		endPosition,
		children,
	} = props

	const scrollY = useSyncExternalStore(
		subscribeToScroll,
		getScrollY,
		getServerScrollY
	)
	const position = scrollY < startPosition ? 0 : Math.min(scrollY, endPosition)

	useEffect(() => {
		applyEffect(position)
	}, [applyEffect, position])

	if (position < startPosition) {
		return null
	}

	return children
}

export default ScrollWrapper
