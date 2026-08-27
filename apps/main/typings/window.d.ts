/** The ouorz-analytics tracker attached to `window` by the analytics script. */
export interface OuorzAnalytics {
	trackView: (url?: string, referrer?: string, uuid?: string) => void
	trackEvent: (
		event_value: string,
		event_type?: string,
		url?: string,
		uuid?: string
	) => void
}

declare global {
	interface Window {
		ouorzAnalytics: OuorzAnalytics
	}
}
