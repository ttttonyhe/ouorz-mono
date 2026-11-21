import { isBrowser } from "~/utilities/environment"

const dummyTrackView = (_url?: string, _referrer?: string, _uuid?: string) => {}
const dummyTrackEvent = (
	_eventValue: string,
	_eventType?: string,
	_url?: string,
	_uuid?: string
) => {}
const dummyReturn = {
	trackView: dummyTrackView,
	trackEvent: dummyTrackEvent,
}

const getAnalytics = () => {
	if (process.env.NODE_ENV === "development" || !isBrowser()) {
		return dummyReturn
	}
	return window.ouorzAnalytics ?? dummyReturn
}

const trackView = (url?: string, referrer?: string, uuid?: string) => {
	getAnalytics().trackView(url, referrer, uuid)
}

const trackEvent = (
	eventValue: string,
	eventType?: string,
	url?: string,
	uuid?: string
) => {
	getAnalytics().trackEvent(eventValue, eventType, url, uuid)
}

const useAnalytics = () => ({
	trackView,
	trackEvent,
})

export default useAnalytics
