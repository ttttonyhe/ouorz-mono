import { type DependencyList, useEffect } from "react"
import useTimeoutFunction from "./useTimeoutFunction"

export type UseDebounceReturn = [() => boolean | null, () => void]

export default function useDebounce(
	fn: Function,
	ms: number = 0,
	deps: DependencyList = []
): UseDebounceReturn {
	const [isReady, cancel, reset] = useTimeoutFunction(fn, ms)

	useEffect(reset, deps)

	return [isReady, cancel]
}
