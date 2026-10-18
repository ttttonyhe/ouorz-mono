import { useRef } from "react"

/**
 * Hook to debounce a function
 *
 * @param {*} func
 * @param {number} delay
 * @return {*} debounced func
 */
const useDebouncedFunction = <Args extends unknown[], Result>(
	func: (...props: Args) => Result,
	delay: number
): ((...props: Args) => Result | undefined) => {
	const lastTime = useRef(Number.NEGATIVE_INFINITY)

	const debouncedFunc = (...props: Args): Result | undefined => {
		const now = Date.now()
		if (lastTime.current > now - delay) return
		lastTime.current = now
		return func(...props)
	}

	return debouncedFunc
}

export default useDebouncedFunction
