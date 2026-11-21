import * as Sentry from "@sentry/nextjs"

const privateOrigins = new Set(
	[process.env.ALCHEMY_API_PATH, process.env.QUICK_NODE_API_PATH].flatMap(
		(endpoint) => {
			const url = endpoint ? URL.parse(endpoint) : null
			return url ? [url.origin] : []
		}
	)
)

const piiDenyList = ["forwarded", "-ip", "remote-", "via", "-user"]

// These providers authenticate with URL paths or query parameters.
const ignoreOutgoingRequests = (url: string) =>
	privateOrigins.has(URL.parse(url)?.origin ?? "")

Sentry.init({
	dsn: process.env.NEXT_PUBLIC_SENTRY_DSN,
	tracesSampleRate: 0.05,
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
	integrations: [
		Sentry.httpIntegration({
			disableIncomingRequestSpans: true,
			ignoreOutgoingRequests,
			maxRequestBodySize: "none",
		}),
		Sentry.nativeNodeFetchIntegration({ ignoreOutgoingRequests }),
	],
})
