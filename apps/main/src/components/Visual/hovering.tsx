import React, { useRef, useState, MouseEvent, CSSProperties } from "react"

interface Props {
	className?: string
	easing?: string
	scale?: number
	speed?: number
	perspective?: number
	max?: number
	children: React.ReactNode
}

const Hover = ({
	className = "",
	easing = "cubic-bezier(0.03, 0.98, 0.52, 0.99)",
	scale = 1,
	speed = 400,
	perspective = 1000,
	max = 10,
	children,
}: Props) => {
	const [tiltStyles, setTiltStyles] = useState<CSSProperties>({})
	const element = useRef<HTMLDivElement>(null)
	const width = useRef(0)
	const height = useRef(0)
	const top = useRef(0)
	const left = useRef(0)
	const updateCall = useRef(null)
	const transitionTimeout = useRef(null)

	const handleOnMouseEnter = () => {
		updateElementPosition()
		setTransition()
	}

	const handleOnMouseMove = (event: MouseEvent) => {
		if (updateCall.current !== null && typeof window !== "undefined") {
			window.cancelAnimationFrame(updateCall.current)
		}
		updateCall.current = requestAnimationFrame(() => updateElementStyle(event))
	}

	const handleOnMouseLeave = () => {
		setTransition()
		handleReset()
	}

	const updateElementStyle = (event: MouseEvent) => {
		const values = getValues(event)

		setTiltStyles((prevStyle) => ({
			...prevStyle,
			transform: `perspective(${perspective}px) rotateX(
        ${values.tiltY}deg) rotateY(${values.tiltX}deg) scale3d(${scale}, ${scale}, ${scale})`,
		}))
	}

	const getValues = (event: MouseEvent) => {
		let x = (event.clientX - left.current) / width.current
		let y = (event.clientY - top.current) / height.current

		x = Math.min(Math.max(x, 0), 1)
		y = Math.min(Math.max(y, 0), 1)

		const tiltX = -1 * parseFloat((max / 2 - x * max).toFixed(2))
		const tiltY = -1 * parseFloat((max / 2 - y * max).toFixed(2))

		const angle =
			Math.atan2(
				event.clientX - (left.current + width.current / 2),
				-(event.clientY - (top.current + height.current / 2))
			) *
			(180 / Math.PI)

		const percentageX = x * 100
		const percentageY = y * 100

		return {
			tiltX,
			tiltY,
			angle,
			percentageX,
			percentageY,
		}
	}

	const updateElementPosition = () => {
		if (!element.current) return
		const rect = element.current.getBoundingClientRect()
		width.current = rect.width
		height.current = rect.height
		top.current = rect.top
		left.current = rect.left
	}

	const setTransition = () => {
		clearTimeout(transitionTimeout.current)

		setTiltStyles((prevStyle) => ({
			...prevStyle,
			transition: `${speed}ms ${easing}`,
		}))

		transitionTimeout.current = setTimeout(() => {
			setTiltStyles((prevStyle) => ({
				...prevStyle,
				transition: "",
			}))
		}, speed)
	}

	const handleReset = () => {
		if (typeof window !== "undefined") {
			window.requestAnimationFrame(() => {
				setTiltStyles((prevStyle) => ({
					...prevStyle,
					transform: `perspective(${perspective}px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`,
				}))
			})
		}
	}

	return (
		<div
			className={`hovering-div transition-shadow ${className}`}
			style={tiltStyles}
			ref={element}
			onMouseEnter={handleOnMouseEnter}
			onMouseMove={handleOnMouseMove}
			onMouseLeave={handleOnMouseLeave}>
			<>{children}</>
		</div>
	)
}

export default Hover
