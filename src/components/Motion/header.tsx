import React, { useEffect } from 'react'
import { useTheme } from 'next-themes'

interface Props {
	componentRef: React.MutableRefObject<HTMLDivElement>
	children: React.ReactNode
}

const BoxShadowTransition = (props: Props) => {
	const { componentRef: ref, children } = props
	const { resolvedTheme } = useTheme()

	const handleScroll = () => {
		if (!ref?.current) return

		let position = window.pageYOffset
		if (!(position >= 0 && position <= 50)) {
			position = 50
		}

		ref.current.style.background = `rgba(${
			resolvedTheme === 'light' ? '255, 255, 255' : '38, 38, 38'
		}, ${position * 0.02})`
		ref.current.style.boxShadow = `0px 1px 3px rgba(0,0,0,${
			position * (20 / 10000)
		})`
	}

	useEffect(() => {
		// invoke scroll handler once to set initial background color
		// after changing theme
		handleScroll()

		window.addEventListener('scroll', handleScroll, { passive: true })

		return () => {
			window.removeEventListener('scroll', handleScroll)
		}
	}, [resolvedTheme])

	return <>{children}</>
}

export default BoxShadowTransition
