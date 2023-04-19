/* eslint-disable camelcase */

// Dummy analytics functions for development environment
const dummyTrackView = (_url?: string, _referrer?: string, _uuid?: string) => {}
const dummyTrackEvent = (
	_event_value: string,
	_event_type?: string,
	_url?: string,
	_uuid?: string
) => {}
const dummyReturn = {
	trackView: dummyTrackView,
	trackEvent: dummyTrackEvent,
}

/**
 *	Get the analytics object
 */
const getAnalytics = () => {
	if (process.env.NODE_ENV === "development" || typeof window === "undefined") {
		return dummyReturn
	}

	const ouorzAnalytics = window.ouorzAnalytics

	if (!ouorzAnalytics) return dummyReturn

	return ouorzAnalytics
}

/**
 * Track a view through ouorz-analytics
 *
 * @param {string} [url]
 * @param {string} [referrer]
 * @param {string} [uuid]
 */
const trackView = (url?: string, referrer?: string, uuid?: string) => {
	const analytics = getAnalytics()
	if (analytics) {
		analytics.trackView(url, referrer, uuid)
	}
}

/**
 * Track an event through ouorz-analytics
 *
 * @param {string} event_value
 * @param {string} [event_type]
 * @param {string} [url]
 * @param {string} [uuid]
 */
const trackEvent = (
	event_value: string,
	event_type?: string,
	url?: string,
	uuid?: string
) => {
	const analytics = getAnalytics()
	if (analytics) {
		analytics.trackEvent(event_value, event_type, url, uuid)
	}
}

/**
 * Hook that encapsulates the analytics functions
 */
const useAnalytics = () => {
	return {
		trackView,
		trackEvent,
	}
}

export default useAnalytics
