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
 *	Get the analytics object
 */
const getAnalytics = () => {
	if (typeof window === 'undefined') {
		return dummyReturn
	}

	const ouorzAnalytics = window.ouorzAnalytics

	if (!ouorzAnalytics) return dummyReturn

	return ouorzAnalytics
}

/**
 * Hook that retrieve tracking methods provided by ouorz-analytics
 */
const useAnalytics = () => {
	return {
		trackView: getAnalytics().trackView,
		trackEvent: getAnalytics().trackEvent,
	}
}

export default useAnalytics
