import { getWebsiteByUuid, getSessionByUuid, createSession } from "lib/queries"
import { getJsonBody, getClientInfo } from "lib/request"
import { uuid, isValidUuid, parseToken } from "lib/crypto"

export async function getSession(req) {
	const { payload } = getJsonBody(req) || {}

	if (!payload) {
		return null
	}

	const { website: website_uuid, hostname, screen, language } = payload
	const cache = req.headers["x-ouorz-analytics-cache"]
	if (!isValidUuid(website_uuid)) return null

	const website = await getWebsiteByUuid(website_uuid)
	if (!website) return null
	const { website_id } = website

	if (cache) {
		const result = await parseToken(cache, "collection")

		if (result?.website_id === website_id && isValidUuid(result.session_uuid)) {
			const session = await getSessionByUuid(result.session_uuid)
			if (
				session?.website_id === website_id &&
				session.session_id === result.session_id
			) {
				return {
					website_id,
					session_id: session.session_id,
					session_uuid: session.session_uuid,
				}
			}
		}
	}

	const { userAgent, browser, os, ip, country, device } = await getClientInfo(
		req,
		payload
	)

	const session_uuid = uuid(website_id, hostname, ip, userAgent)

	let session = await getSessionByUuid(session_uuid)
	if (session && session.website_id !== website_id) return null

	if (!session) {
		try {
			session = await createSession(website_id, {
				session_uuid,
				hostname,
				browser,
				os,
				screen,
				language,
				country,
				device,
			})
		} catch (e) {
			if (e.code !== "P2002") {
				throw e
			}
			session = await getSessionByUuid(session_uuid)
			if (!session || session.website_id !== website_id) throw e
		}
	}

	const { session_id } = session

	return {
		website_id,
		session_id,
		session_uuid,
	}
}
