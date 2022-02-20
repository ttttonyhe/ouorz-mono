import { useState } from 'react'

/**
 * Hook to debounce a function
 *
 * @param {*} func
 * @param {number} delay
 * @return {*} debounced func
 */
const useDebounce = <T>(
	func: (...props: any[]) => T,
	delay: number
): typeof func => {
	const [lastTime, setLastTime] = useState(0)

	const debouncedFunc = (...props: any[]): T => {
		if (lastTime > Date.now() - delay) return
		setLastTime(Date.now())
		return func(...props)
	}

	return debouncedFunc
}

export default useDebounce
