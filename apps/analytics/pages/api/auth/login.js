import { checkPassword, createSecureToken, hash } from "lib/crypto"
import { publicAccount } from "lib/auth"
import { getAccountByUsername } from "lib/queries"
import { ok, unauthorized, badRequest, methodNotAllowed } from "lib/response"

export default async (req, res) => {
	if (req.method !== "POST") return methodNotAllowed(res)
	const { username, password } = req.body || {}

	if (
		typeof username !== "string" ||
		!username ||
		typeof password !== "string" ||
		!password
	) {
		return badRequest(res)
	}

	const account = await getAccountByUsername(username)

	if (account && (await checkPassword(password, account.password))) {
		const user = publicAccount(account)
		const token = await createSecureToken({
			user_id: account.user_id,
			password_version: hash(account.password),
		})

		return ok(res, { token, user })
	}

	return unauthorized(res)
}
