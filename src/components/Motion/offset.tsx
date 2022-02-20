import React from 'react'
import ScrollWrapper from './scroll'

interface Props {
	componentRef: React.MutableRefObject<HTMLDivElement>
	children: React.ReactNode
}

const OffsetTransition = (props: Props) => {
	const { componentRef: ref, children } = props

	const handler = (position: number) => {
		if (!ref?.current) return

		ref.current.style.transform = `translateY(${(50 - position) * 0.5}%)`
		ref.current.style.opacity = `${position * 0.02}`
	}

	return (
		<ScrollWrapper handler={handler} endPosition={50}>
			{children}
		</ScrollWrapper>
	)
}

export default OffsetTransition
