import { useSyncExternalStore } from "react"

const subscribe = () => () => {}
const getSnapshot = () => true
const getServerSnapshot = () => false

// False during SSR and hydration, true once mounted on the client.
const useMounted = (): boolean =>
	useSyncExternalStore(subscribe, getSnapshot, getServerSnapshot)

export default useMounted
