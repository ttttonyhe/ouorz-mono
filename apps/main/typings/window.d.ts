export {}

declare global {
	interface Window {
		// ouorz-analytics tracker
		ouorzAnalytics: {
			trackView: (url?: string, referrer?: string, uuid?: string) => void
			trackEvent: (
				event_value: string,
				event_type?: string,
				url?: string,
				uuid?: string
			) => void
		}
	}
}
