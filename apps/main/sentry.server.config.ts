import * as Sentry from "@sentry/nextjs"

const privateOrigins = new Set(
	[
		process.env.ALCHEMY_API_PATH,
		process.env.QUICK_NODE_API_PATH,
		process.env.JMS_API_PATH,
	].flatMap((endpoint) => {
		const url = endpoint && URL.parse(endpoint)
		return url ? [url.origin] : []
	})
)

// These providers authenticate with URL paths or query parameters.
const ignoreOutgoingRequests = (url: string) =>
	privateOrigins.has(URL.parse(url)?.origin ?? "")

Sentry.init({
	dsn: process.env.NEXT_PUBLIC_SENTRY_DSN,
	tracesSampleRate: 0.05,
	integrations: [
		Sentry.httpIntegration({
			disableIncomingRequestSpans: true,
			ignoreOutgoingRequests,
			maxIncomingRequestBodySize: "none",
		}),
		Sentry.nativeNodeFetchIntegration({ ignoreOutgoingRequests }),
	],
})
