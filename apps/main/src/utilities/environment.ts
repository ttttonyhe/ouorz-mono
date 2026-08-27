/**
 * Single owner for environment and capability detection. Every other module
 * branches on these predicates instead of re-deriving them from `typeof`.
 */

/** True while running in a browser, false during server rendering. */
export const isBrowser = (): boolean => typeof window !== "undefined"

/** True when the browser exposes the View Transitions API. */
export const supportsViewTransitions = (): boolean =>
	isBrowser() && typeof document.startViewTransition === "function"
