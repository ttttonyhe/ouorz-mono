import { useSyncExternalStore } from "react"
import type { ReactNode } from "react"

const ClientOnly = ({ children }: { children: ReactNode }) => {
	const isServer = useSyncExternalStore(
		() => () => {},
		() => false,
		() => true
	)

	return isServer ? null : children
}

export default ClientOnly
