import * as Sentry from "@sentry/nextjs"

export async function register() {
	if (process.env.NEXT_RUNTIME === "nodejs") {
		Sentry.init({
			dsn: process.env.NEXT_PUBLIC_SENTRY_DSN,
			tracesSampleRate: 0.05,
		})
	}

	if (process.env.NEXT_RUNTIME === "edge") {
		Sentry.init({
			dsn: process.env.NEXT_PUBLIC_SENTRY_DSN,
			tracesSampleRate: 0.05,
		})
	}
}

export const onRequestError = Sentry.captureRequestError
