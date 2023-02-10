import React, { useState, useEffect } from 'react'
import { useTheme } from 'next-themes'

interface Props {
	handler: (postition: number) => void
	startPosition?: number
	endPosition: number
	children: React.ReactNode
}

const ScrollWrapper = (props: Props) => {
	const { handler: applyEffect, startPosition, endPosition, children } = props
	const { resolvedTheme } = useTheme()
	const [yOffset, setYOffset] = useState(0)

	const handler = () => {
		let position = window.pageYOffset

		if (position < startPosition) {
			position = 0
		}

		if (position >= endPosition) {
			position = endPosition
		}

		setYOffset(position)
		applyEffect(position)
	}

	useEffect(() => {
		// invoke scroll handler once after changing theme
		handler()

		window.addEventListener('scroll', handler, { passive: true })

		return () => {
			window.removeEventListener('scroll', handler)
		}
	}, [resolvedTheme])

	if (yOffset < startPosition) {
		return null
	}

	return <>{children}</>
}

export default ScrollWrapper
