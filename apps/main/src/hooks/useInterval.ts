import { useEffect, useRef } from "react"

type IntervalCallback = () => void

const useInterval = (callback: IntervalCallback, delay?: number | null) => {
	const savedCallback = useRef<IntervalCallback>(() => {})

	useEffect(() => {
		savedCallback.current = callback
	})

	useEffect(() => {
		if (delay === null) return

		const interval = setInterval(() => savedCallback.current(), delay ?? 0)
		return () => clearInterval(interval)
	}, [delay])
}

export default useInterval
