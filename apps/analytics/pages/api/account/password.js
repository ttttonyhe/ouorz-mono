import { getAccountById, updateAccount } from "lib/queries"
import { useAuth } from "lib/middleware"
import {
	badRequest,
	methodNotAllowed,
	ok,
	unauthorized,
	notFound,
} from "lib/response"
import { checkPassword, hashPassword } from "lib/crypto"
import { publicAccount } from "lib/auth"

export default async (req, res) => {
	if (!(await useAuth(req, res))) return
	if (req.method !== "POST") return methodNotAllowed(res)

	const { user_id: auth_user_id, is_admin } = req.auth
	const { user_id, current_password, new_password } = req.body || {}
	if (
		!Number.isSafeInteger(user_id) ||
		user_id <= 0 ||
		typeof current_password !== "string" ||
		typeof new_password !== "string" ||
		!new_password ||
		Buffer.byteLength(new_password) > 72
	)
		return badRequest(res)

	if (!is_admin && user_id !== auth_user_id) {
		return unauthorized(res)
	}

	const account = await getAccountById(user_id)
	if (!account) return notFound(res)
	const valid = checkPassword(current_password, account.password)

	if (!valid) {
		return badRequest(res, "Current password is incorrect")
	}

	const password = hashPassword(new_password)

	const updated = await updateAccount(user_id, { password })

	return ok(res, publicAccount(updated))
}
