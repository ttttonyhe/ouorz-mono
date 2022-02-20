import React, { useEffect } from 'react'

interface Props {
	componentRef: React.MutableRefObject<HTMLDivElement>
	children: React.ReactNode
}

const OffsetTransition = (props: Props) => {
	const { componentRef: ref, children } = props

	const handleScroll = () => {
		if (!ref?.current) return

		let position = window.pageYOffset
		if (!(position >= 0 && position <= 50)) {
			position = 50
		}

		ref.current.style.transform = `translateY(${(50 - position) * 0.5}%)`
		ref.current.style.opacity = `${position * 0.02}`
	}

	useEffect(() => {
		window.addEventListener('scroll', handleScroll, { passive: true })

		return () => {
			window.removeEventListener('scroll', handleScroll)
		}
	}, [])

	return <>{children}</>
}

export default OffsetTransition
