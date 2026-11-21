import type { NextRouter } from "next/router"
import { useEffect } from "react"
import { supportsViewTransitions } from "~/utilities/environment"

export const getViewTransitionName = (title: string): string =>
	`page-title-${title
		.toLowerCase()
		.replaceAll(/[^a-z0-9]/gu, "-")
		.replaceAll(/-+/gu, "-")
		.replaceAll(/^-|-$/gu, "")}`

const waitForRouteSettlement = (router: NextRouter): Promise<void> =>
	new Promise<void>((resolve) => {
		const finish = () => {
			router.events.off("routeChangeComplete", finish)
			router.events.off("routeChangeError", finish)
			resolve()
		}
		router.events.on("routeChangeComplete", finish)
		router.events.on("routeChangeError", finish)
	})

export const navigateWithTransition = (
	router: NextRouter,
	href: string
): void => {
	if (supportsViewTransitions()) {
		document.startViewTransition(() => {
			const settled = waitForRouteSettlement(router)
			void router.push(href)
			return settled
		})
	} else {
		void router.push(href)
	}
}

export const useViewTransitionRouter = (router: NextRouter): void => {
	useEffect(() => {
		if (!supportsViewTransitions()) return

		const handleBeforePopState = (): boolean => {
			document.startViewTransition(() => waitForRouteSettlement(router))
			return true
		}

		router.beforePopState(handleBeforePopState)

		return () => {
			router.beforePopState(() => true)
		}
	}, [router])
}
