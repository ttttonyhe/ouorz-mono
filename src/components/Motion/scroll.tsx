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
	const { resolvedTheme } = useTheme()

	const handler = () => {
		let position = window.pageYOffset
		if (!(position >= (startPosition || 0) && position <= endPosition)) {
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
	}, [resolvedTheme])

	return <>{children}</>
}

export default ScrollWrapper
