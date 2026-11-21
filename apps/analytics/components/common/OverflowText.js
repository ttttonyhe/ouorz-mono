import React, { useCallback, useEffect, useRef, useState } from "react"
import { Tooltip } from "react-tooltip"
import styles from "./OverflowText.module.css"

const OverflowText = ({ children, tooltipId }) => {
	const measureEl = useRef(null)
	const [isOverflown, setIsOverflown] = useState(false)

	const measure = useCallback((el) => {
		if (!el) return
		setIsOverflown(el.scrollWidth > el.clientWidth)
	}, [])

	useEffect(() => {
		const el = measureEl.current
		if (!el) return

		measure(el)
		const observer = new ResizeObserver(() => measure(el))
		observer.observe(el)
		return () => observer.disconnect()
	}, [children, measure])

	return (
		<span
			ref={measureEl}
			data-tooltip-id={isOverflown ? tooltipId : undefined}
			data-tooltip-content={isOverflown ? String(children) : undefined}
			className={styles.root}
		>
			{children}
			{isOverflown && <Tooltip id={tooltipId} />}
		</span>
	)
}

export default OverflowText
