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

		ref.current.style.transform = `translateY(${(125 - position) * 0.5}%)`
		ref.current.style.opacity = `${(position - 25) * 0.01}`
	}

	return (
		<ScrollWrapper handler={handler} startPosition={50} endPosition={125}>
			{children}
		</ScrollWrapper>
	)
}

export default OffsetTransition
