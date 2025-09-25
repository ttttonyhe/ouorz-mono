import type { ReactNode } from "react"
import { useSyncExternalStore } from "react"

const ClientOnly = ({ children }: { children: ReactNode }) => {
	const isServer = useSyncExternalStore(
		() => () => {},
		() => false,
		() => true
	)

	return isServer ? null : children
}

export default ClientOnly
