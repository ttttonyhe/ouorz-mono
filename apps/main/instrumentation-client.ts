import {
	captureRequestError,
	captureRouterTransitionStart,
	init,
	replayIntegration,
} from "@sentry/nextjs"
import { z } from "zod"

z.config({ jitless: true })

init({
	dsn: process.env.NEXT_PUBLIC_SENTRY_DSN,
	integrations: [replayIntegration()],
	tracesSampleRate: 0.05,
	replaysSessionSampleRate: 0.05,
	replaysOnErrorSampleRate: 0.05,
})

export const onRouterTransitionStart = captureRouterTransitionStart

export const onRequestError = captureRequestError
