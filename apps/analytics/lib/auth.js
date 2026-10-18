import { hash, parseSecureToken, parseToken } from "./crypto"
import { SHARE_TOKEN_HEADER } from "./constants"
import { getAccountById, getWebsiteById } from "./queries"

export function publicAccount({
	user_id,
	username,
	is_admin,
	created_at,
	updated_at,
}) {
	return { user_id, username, is_admin, created_at, updated_at }
}

function getBearerToken(req) {
	const authorization = req.headers.authorization
	const match =
		typeof authorization === "string" && authorization.match(/^Bearer (\S+)$/i)
	return match ? match[1] : null
}

async function getTokenAccount(token) {
	if (!Number.isSafeInteger(token?.user_id) || token.user_id <= 0) return null

	const account = await getAccountById(token.user_id)
	if (!account || token.password_version !== hash(account.password)) return null
	return account
}

export async function getAuthToken(req) {
	const account = await getTokenAccount(
		await parseSecureToken(getBearerToken(req))
	)
	return account ? publicAccount(account) : null
}

export async function allowStatsServiceQuery(req) {
	if (req.method !== "GET") return false
	if (Object.values(req.query).some((value) => typeof value !== "string"))
		return false
	const websiteId = Number(req.query.id)
	if (!Number.isSafeInteger(websiteId) || websiteId <= 0) return false

	const token = await parseToken(getBearerToken(req), "stats-service")
	if (token?.website_id !== websiteId) return false
	const [account, website] = await Promise.all([
		getTokenAccount(token),
		getWebsiteById(websiteId),
	])
	return (
		!!account &&
		!!website &&
		token.account_version === (account.updated_at?.toISOString() ?? null) &&
		token.is_admin === account.is_admin &&
		(account.is_admin || website.user_id === account.user_id)
	)
}

export async function allowQuery(req, skipToken = false) {
	const websiteId = Number(req.query.id)
	if (!Number.isSafeInteger(websiteId) || websiteId <= 0) return false
	if (Object.values(req.query).some((value) => typeof value !== "string"))
		return false

	const website = await getWebsiteById(websiteId)
	if (!website) return false

	const token = req.headers[SHARE_TOKEN_HEADER]
	if (token && !skipToken) {
		const share = await parseToken(token, "share")
		if (
			website.share_id &&
			share?.website_id === websiteId &&
			share.share_id === website.share_id
		) {
			return true
		}
	}

	const account = await getAuthToken(req)
	return !!account && (account.is_admin || website.user_id === account.user_id)
}
