import {
	captureRequestError,
	captureRouterTransitionStart,
	init,
	replayIntegration,
} from "@sentry/nextjs"

init({
	dsn: process.env.NEXT_PUBLIC_SENTRY_DSN,
	integrations: [replayIntegration()],
	tracesSampleRate: 0.05,
	replaysSessionSampleRate: 0.05,
	replaysOnErrorSampleRate: 0.05,
})

export const onRouterTransitionStart = captureRouterTransitionStart

export const onRequestError = captureRequestError
