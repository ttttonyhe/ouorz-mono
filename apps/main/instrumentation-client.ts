import {
	captureRouterTransitionStart,
	init,
	replayIntegration,
} from "@sentry/nextjs"
import { z } from "zod"

z.config({ jitless: true })

const piiDenyList = ["forwarded", "-ip", "remote-", "via", "-user"]

init({
	dsn: process.env.NEXT_PUBLIC_SENTRY_DSN,
	integrations: [replayIntegration()],
	tracesSampleRate: 0.05,
	replaysSessionSampleRate: 0.05,
	replaysOnErrorSampleRate: 0.05,
	dataCollection: {
		userInfo: false,
		cookies: false,
		httpHeaders: {
			request: { deny: piiDenyList },
			response: { deny: piiDenyList },
		},
		httpBodies: [],
		urlQueryParams: { deny: piiDenyList },
		genAI: { inputs: false, outputs: false },
		databaseQueryData: false,
		queues: false,
		graphQL: { document: false, variables: false },
	},
})

export const onRouterTransitionStart = captureRouterTransitionStart
