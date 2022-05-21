/* eslint-disable camelcase */

// Dummy functions for development environment
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
 * Tracking methods provided by ouorz-analytics
 */
const analytics = () => {
	// return dummy functions when in development or window is not available
	if (process.env.NODE_ENV === 'development' || typeof window === 'undefined') {
		return dummyReturn
	}

	const ouorzAnalytics = window.ouorzAnalytics

	if (!ouorzAnalytics) return dummyReturn

	// return a function that takes a config object
	return ouorzAnalytics
}

export default analytics()
