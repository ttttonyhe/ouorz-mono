import {
	getAccountById,
	getAccountByUsername,
	updateAccount,
	createAccount,
} from "lib/queries"
import { useAuth } from "lib/middleware"
import { hashPassword } from "lib/crypto"
import { publicAccount } from "lib/auth"
import {
	ok,
	unauthorized,
	methodNotAllowed,
	badRequest,
	notFound,
} from "lib/response"

export default async (req, res) => {
	if (!(await useAuth(req, res))) return
	if (!req.auth.is_admin) return unauthorized(res)
	if (req.method !== "POST") return methodNotAllowed(res)

	const { user_id, username, password, is_admin } = req.body || {}
	if (user_id !== undefined && (!Number.isSafeInteger(user_id) || user_id <= 0))
		return badRequest(res)
	if (
		username !== undefined &&
		(typeof username !== "string" || !username.trim() || username.length > 255)
	)
		return badRequest(res)
	if (
		password !== undefined &&
		(typeof password !== "string" || Buffer.byteLength(password) > 72)
	)
		return badRequest(res)
	if (is_admin !== undefined && typeof is_admin !== "boolean")
		return badRequest(res)

	if (username) {
		const existing = await getAccountByUsername(username)
		if (existing && existing.user_id !== user_id)
			return badRequest(res, "Account already exists")
	}

	try {
		if (user_id) {
			if (!(await getAccountById(user_id))) return notFound(res)
			const updated = await updateAccount(user_id, {
				username,
				is_admin,
				password: password ? hashPassword(password) : undefined,
			})
			return ok(res, publicAccount(updated))
		}

		if (!username || !password) return badRequest(res)
		const created = await createAccount({
			username,
			password: hashPassword(password),
			is_admin,
		})
		return ok(res, publicAccount(created))
	} catch (error) {
		if (error.code === "P2002") return badRequest(res, "Account already exists")
		throw error
	}
}
