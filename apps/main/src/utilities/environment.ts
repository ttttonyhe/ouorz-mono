// Single owner for environment predicates so other modules avoid runtime typeof checks.
export const isBrowser = (): boolean => typeof window !== "undefined"

export const supportsViewTransitions = (): boolean =>
	isBrowser() && typeof document.startViewTransition === "function"
