import { useAuth } from "lib/middleware"
import { ok, methodNotAllowed } from "lib/response"

export default async (req, res) => {
	if (!(await useAuth(req, res))) return

	if (req.method !== "GET") return methodNotAllowed(res)
	return ok(res, req.auth)
}
