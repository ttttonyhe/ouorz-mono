import { useSyncExternalStore } from "react"

const subscribe = () => () => {}
const getSnapshot = () => true
const getServerSnapshot = () => false

/**
 * False while the markup is rendered on the server and during hydration, true
 * once the component is running in the browser. Replaces the
 * `useState(false)` + `useEffect(() => setMounted(true))` pair with a single
 * source of truth that React itself owns.
 */
const useMounted = (): boolean =>
	useSyncExternalStore(subscribe, getSnapshot, getServerSnapshot)

export default useMounted
