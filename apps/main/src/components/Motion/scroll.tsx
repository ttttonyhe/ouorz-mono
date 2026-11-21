import type React from "react"
import { useEffect, useEffectEvent } from "react"

interface Props {
	handler: (position: number) => void
	startPosition?: number
	endPosition: number
	children: React.ReactNode
}

const ScrollWrapper = ({
	handler,
	startPosition = 0,
	endPosition,
	children,
}: Props) => {
	const onApply = useEffectEvent(handler)

	useEffect(() => {
		let rafId: number | null = null

		const apply = () => {
			rafId = null
			const y = window.scrollY
			onApply(y < startPosition ? 0 : Math.min(y, endPosition))
		}

		const onScroll = () => {
			if (rafId === null) rafId = window.requestAnimationFrame(apply)
		}

		apply()
		window.addEventListener("scroll", onScroll, { passive: true })
		return () => {
			window.removeEventListener("scroll", onScroll)
			if (rafId !== null) window.cancelAnimationFrame(rafId)
		}
	}, [startPosition, endPosition])

	return children
}

export default ScrollWrapper
