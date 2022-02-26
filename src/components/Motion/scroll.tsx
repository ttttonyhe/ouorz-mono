import React, { useEffect } from 'react'
import { useTheme } from 'next-themes'

interface Props {
	handler: (postition: number) => void
	startPosition?: number
	endPosition: number
	children: React.ReactNode
}

const ScrollWrapper = (props: Props) => {
	const { handler: applyEffect, startPosition, endPosition, children } = props
	const { theme } = useTheme()

	const handler = () => {
		let position = window.pageYOffset

		if (position < startPosition) {
			position = 0
		}

		if (position >= endPosition) {
			position = endPosition
		}

		applyEffect(position)
	}

	useEffect(() => {
		// invoke scroll handler once after changing theme
		handler()

		window.addEventListener('scroll', handler, { passive: true })

		return () => {
			window.removeEventListener('scroll', handler)
		}
	}, [theme])

	return <>{children}</>
}

export default ScrollWrapper
