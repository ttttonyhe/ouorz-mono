import { updateWebsite, createWebsite, getWebsiteById } from "lib/queries"
import { useAuth } from "lib/middleware"
import { uuid, getRandomChars } from "lib/crypto"
import {
	ok,
	unauthorized,
	methodNotAllowed,
	badRequest,
	notFound,
} from "lib/response"

export default async (req, res) => {
	if (!(await useAuth(req, res))) return
	if (req.method !== "POST") return methodNotAllowed(res)

	const { user_id, is_admin } = req.auth
	const { website_id, enable_share_url, name, domain } = req.body || {}
	if (
		website_id !== undefined &&
		(!Number.isSafeInteger(website_id) || website_id <= 0)
	)
		return badRequest(res)
	if (
		typeof name !== "string" ||
		!name.trim() ||
		name.length > 100 ||
		(domain != null && (typeof domain !== "string" || domain.length > 500)) ||
		(enable_share_url !== undefined && typeof enable_share_url !== "boolean")
	)
		return badRequest(res)

	if (website_id) {
		const website = await getWebsiteById(website_id)
		if (!website) return notFound(res)

		if (website.user_id !== user_id && !is_admin) {
			return unauthorized(res)
		}

		let { share_id } = website

		if (enable_share_url) {
			share_id = share_id || getRandomChars(32)
		} else {
			share_id = null
		}

		await updateWebsite(website_id, { name, domain, share_id })

		return ok(res)
	} else {
		const website_uuid = uuid()
		const share_id = enable_share_url ? getRandomChars(32) : null
		const website = await createWebsite(user_id, {
			website_uuid,
			name,
			domain,
			share_id,
		})

		return ok(res, website)
	}
}
