import { getAccountById, deleteAccount } from "lib/queries"
import { useAuth } from "lib/middleware"
import { publicAccount } from "lib/auth"
import {
	methodNotAllowed,
	ok,
	unauthorized,
	badRequest,
	notFound,
} from "lib/response"

export default async (req, res) => {
	if (!(await useAuth(req, res))) return

	const { is_admin } = req.auth
	const { id } = req.query
	const user_id = +id
	if (!Number.isSafeInteger(user_id) || user_id <= 0) return badRequest(res)

	if (!is_admin) {
		return unauthorized(res)
	}

	if (req.method === "GET") {
		const account = await getAccountById(user_id)

		return account ? ok(res, publicAccount(account)) : notFound(res)
	}

	if (req.method === "DELETE") {
		if (!(await getAccountById(user_id))) return notFound(res)
		await deleteAccount(user_id)

		return ok(res)
	}

	return methodNotAllowed(res)
}
