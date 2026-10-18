import { getWebsiteByShareId } from "lib/queries"
import { ok, notFound, methodNotAllowed, badRequest } from "lib/response"
import { createToken } from "lib/crypto"

export default async (req, res) => {
	const { id } = req.query
	if (typeof id !== "string" || !id || id.length > 64) return badRequest(res)

	if (req.method === "GET") {
		const website = await getWebsiteByShareId(id)

		if (website) {
			const websiteId = website.website_id
			const token = await createToken(
				{ website_id: websiteId, share_id: website.share_id },
				"share"
			)

			return ok(res, { websiteId, token })
		}

		return notFound(res)
	}

	return methodNotAllowed(res)
}
