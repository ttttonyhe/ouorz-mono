import { useCallback } from "react"
import { useRouter } from "next/router"
import { get, post, put, del, getItem, removeItem } from "lib/web"
import { AUTH_TOKEN, SHARE_TOKEN_HEADER } from "lib/constants"
import useStore, { setUser } from "store/app"

const selector = (state) => state.shareToken

function parseHeaders(input = {}, { authToken, shareToken }) {
	const headers = { ...input }
	if (authToken) {
		headers.authorization = `Bearer ${authToken}`
	}

	if (shareToken) {
		headers[SHARE_TOKEN_HEADER] = shareToken.token
	}

	return headers
}

export default function useApi() {
	const router = useRouter()
	const { basePath } = router
	const authToken = getItem(AUTH_TOKEN)
	const shareToken = useStore(selector)
	const handleResponse = useCallback(
		async (response) => {
			if (
				response.status === 401 &&
				authToken &&
				getItem(AUTH_TOKEN) === authToken
			) {
				const verification = await get(
					`${basePath}/api/auth/verify`,
					undefined,
					{
						authorization: `Bearer ${authToken}`,
					}
				)
				if (verification.status === 401 && getItem(AUTH_TOKEN) === authToken) {
					removeItem(AUTH_TOKEN)
					setUser(null)
					await router.replace("/login")
				}
			}
			return response
		},
		[authToken, router, basePath]
	)

	return {
		get: useCallback(
			async (url, params, headers) => {
				return get(
					`${basePath}/api${url}`,
					params,
					parseHeaders(headers, { authToken, shareToken })
				).then(handleResponse)
			},
			[authToken, shareToken, basePath, handleResponse]
		),

		post: useCallback(
			async (url, params, headers) => {
				return post(
					`${basePath}/api${url}`,
					params,
					parseHeaders(headers, { authToken, shareToken })
				).then(handleResponse)
			},
			[authToken, shareToken, basePath, handleResponse]
		),

		put: useCallback(
			async (url, params, headers) => {
				return put(
					`${basePath}/api${url}`,
					params,
					parseHeaders(headers, { authToken, shareToken })
				).then(handleResponse)
			},
			[authToken, shareToken, basePath, handleResponse]
		),

		del: useCallback(
			async (url, params, headers) => {
				return del(
					`${basePath}/api${url}`,
					params,
					parseHeaders(headers, { authToken, shareToken })
				).then(handleResponse)
			},
			[authToken, shareToken, basePath, handleResponse]
		),
	}
}
