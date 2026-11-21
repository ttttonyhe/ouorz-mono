import type React from "react"
import { type MouseEvent, useEffect, useRef } from "react"

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
	const element = useRef<HTMLDivElement>(null)
	const rectRef = useRef({ width: 0, height: 0, top: 0, left: 0 })
	const rafRef = useRef<number | null>(null)
	const timeoutRef = useRef<number | null>(null)

	useEffect(
		() => () => {
			if (rafRef.current !== null) window.cancelAnimationFrame(rafRef.current)
			if (timeoutRef.current !== null) window.clearTimeout(timeoutRef.current)
		},
		[]
	)

	const applyTransition = () => {
		const node = element.current
		if (!node) return
		if (timeoutRef.current !== null) window.clearTimeout(timeoutRef.current)
		node.style.transition = `${speed}ms ${easing}`
		timeoutRef.current = window.setTimeout(() => {
			if (element.current) element.current.style.transition = ""
		}, speed)
	}

	const handleOnMouseEnter = () => {
		if (!element.current) return
		const rect = element.current.getBoundingClientRect()
		rectRef.current = {
			width: rect.width,
			height: rect.height,
			top: rect.top,
			left: rect.left,
		}
		applyTransition()
	}

	const handleOnMouseMove = (event: MouseEvent) => {
		const { clientX, clientY } = event
		if (rafRef.current !== null) window.cancelAnimationFrame(rafRef.current)
		rafRef.current = window.requestAnimationFrame(() => {
			const node = element.current
			const { width, height, top, left } = rectRef.current
			if (!node || width === 0 || height === 0) return

			const x = Math.min(Math.max((clientX - left) / width, 0), 1)
			const y = Math.min(Math.max((clientY - top) / height, 0), 1)
			const tiltX = -Number((max / 2 - x * max).toFixed(2))
			const tiltY = -Number((max / 2 - y * max).toFixed(2))
			node.style.transform = `perspective(${perspective}px) rotateX(${tiltY}deg) rotateY(${tiltX}deg) scale3d(${scale}, ${scale}, ${scale})`
		})
	}

	const handleOnMouseLeave = () => {
		applyTransition()
		if (rafRef.current !== null) window.cancelAnimationFrame(rafRef.current)
		rafRef.current = window.requestAnimationFrame(() => {
			if (element.current) {
				element.current.style.transform = `perspective(${perspective}px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`
			}
		})
	}

	return (
		<div
			className={`hovering-div transition-shadow ${className}`}
			ref={element}
			onMouseEnter={handleOnMouseEnter}
			onMouseMove={handleOnMouseMove}
			onMouseLeave={handleOnMouseLeave}>
			{children}
		</div>
	)
}

export default Hover
