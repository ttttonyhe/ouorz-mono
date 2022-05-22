/* eslint-disable camelcase */
import { useEffect, useState } from 'react'

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
const useAnalytics = () => {
	const [ouorzAnalytics, setOuorzAnalytics] = useState(dummyReturn)

	useEffect(() => {
		const windowOuorzAnalytics = window.ouorzAnalytics

		if (
			process.env.NODE_ENV === 'development' ||
			typeof window === 'undefined' ||
			!windowOuorzAnalytics
		) {
			console.error('Analytics is not available')
			return
		}

		setOuorzAnalytics(windowOuorzAnalytics)
	}, [])

	return ouorzAnalytics
}

export default useAnalytics
