import { useTheme } from "next-themes"
import type React from "react"
import { useCallback, useEffect, useState } from "react"

interface Props {
	handler: (position: number) => void
	startPosition?: number
	endPosition: number
	children: React.ReactNode
}

const ScrollWrapper = (props: Props) => {
	const {
		handler: applyEffect,
		startPosition = 0,
		endPosition,
		children,
	} = props
	const { resolvedTheme } = useTheme()
	const [yOffset, setYOffset] = useState(0)

	const handler = useCallback(() => {
		let position = window.scrollY

		if (position < startPosition) {
			position = 0
		}

		if (position >= endPosition) {
			position = endPosition
		}

		setYOffset(position)
		applyEffect(position)
	}, [applyEffect, endPosition, startPosition])

	useEffect(() => {
		// invoke scroll handler once after changing theme
		handler()

		window.addEventListener("scroll", handler, { passive: true })

		return () => {
			window.removeEventListener("scroll", handler)
		}
	}, [handler, resolvedTheme])

	if (yOffset < startPosition) {
		return null
	}

	return <>{children}</>
}

export default ScrollWrapper
