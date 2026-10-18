import { getAccounts } from "lib/queries"
import { useAuth } from "lib/middleware"
import { publicAccount } from "lib/auth"
import { ok, unauthorized, methodNotAllowed } from "lib/response"

export default async (req, res) => {
	if (!(await useAuth(req, res))) return

	const { is_admin } = req.auth

	if (!is_admin) {
		return unauthorized(res)
	}

	if (req.method === "GET") {
		const accounts = await getAccounts()

		return ok(res, accounts.map(publicAccount))
	}

	return methodNotAllowed(res)
}
