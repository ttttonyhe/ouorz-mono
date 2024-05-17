import * as Sentry from "@sentry/nextjs"

Sentry.init({
	dsn: process.env.NEXT_PUBLIC_SENTRY_DSN,
	integrations: [Sentry.replayIntegration()],
	tracesSampleRate: 0.1,
	replaysSessionSampleRate: 0.1,
	replaysOnErrorSampleRate: 0.1,
})
