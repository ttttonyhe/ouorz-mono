import { useEffect, useEffectEvent } from "react"

const useInterval = (callback: () => void, delay?: number | null) => {
	const onTick = useEffectEvent(callback)

	useEffect(() => {
		if (delay === null) return

		const interval = setInterval(() => onTick(), delay ?? 0)
		return () => clearInterval(interval)
	}, [delay])
}

export default useInterval
