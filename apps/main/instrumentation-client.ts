import * as Sentry from "@sentry/nextjs"

Sentry.init({
	dsn: process.env.NEXT_PUBLIC_SENTRY_DSN,
	integrations: [Sentry.replayIntegration()],
	tracesSampleRate: 0.05,
	replaysSessionSampleRate: 0.05,
	replaysOnErrorSampleRate: 0.05,
})

export const onRouterTransitionStart = Sentry.captureRouterTransitionStart

export const onRequestError = Sentry.captureRequestError
