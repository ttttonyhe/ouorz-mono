import type { NextRouter } from "next/router"
import { useEffect } from "react"

/**
 * Generates a CSS-safe view-transition-name from a title string.
 * The name must be a valid CSS ident (no spaces, special chars, etc.)
 */
export const getViewTransitionName = (title: string): string => {
	return `page-title-${title
		.toLowerCase()
		.replace(/[^a-z0-9]/g, "-")
		.replace(/-+/g, "-")
		.replace(/^-|-$/g, "")}`
}

/**
 * Checks if the View Transitions API is supported.
 */
const isViewTransitionSupported = (): boolean => {
	return (
		typeof document !== "undefined" &&
		"startViewTransition" in document &&
		typeof document.startViewTransition === "function"
	)
}

/**
 * Navigates to a URL with view transition support.
 * Waits for Next.js route change to complete before finishing the transition.
 * Falls back to regular navigation if View Transitions API is not supported.
 */
export const navigateWithTransition = (
	router: NextRouter,
	href: string
): void => {
	if (isViewTransitionSupported()) {
		document.startViewTransition(() => {
			return new Promise<void>((resolve) => {
				const handleComplete = () => {
					router.events.off("routeChangeComplete", handleComplete)
					resolve()
				}
				router.events.on("routeChangeComplete", handleComplete)
				router.push(href)
			})
		})
	} else {
		router.push(href)
	}
}

/**
 * Hook to enable view transitions for browser back/forward navigation.
 * Should be used in _app.tsx or a layout component.
 */
export const useViewTransitionRouter = (router: NextRouter): void => {
	useEffect(() => {
		if (!isViewTransitionSupported()) return

		const handleBeforePopState = (): boolean => {
			// Start view transition and let Next.js handle navigation normally
			document.startViewTransition(() => {
				return new Promise<void>((resolve) => {
					const handleComplete = () => {
						router.events.off("routeChangeComplete", handleComplete)
						resolve()
					}
					router.events.on("routeChangeComplete", handleComplete)
				})
			})
			// Return true to let Next.js handle the navigation
			return true
		}

		router.beforePopState(handleBeforePopState)

		return () => {
			router.beforePopState(() => true)
		}
	}, [router])
}
