import cors from "cors"
import { getSession } from "./session"
import { getAuthToken } from "./auth"
import { unauthorized, badRequest, serverError } from "./response"

export function createMiddleware(middleware) {
	return (req, res) =>
		new Promise((resolve, reject) => {
			const finish = (error, proceed = false) => {
				res.off("finish", ended)
				res.off("close", ended)
				if (error) reject(error)
				else resolve(proceed)
			}
			const ended = () => finish()
			res.once("finish", ended)
			res.once("close", ended)

			try {
				Promise.resolve(
					middleware(req, res, (error) => finish(error, !res.writableEnded))
				).catch(finish)
			} catch (error) {
				finish(error)
			}
		})
}

export const useCors = createMiddleware(cors())

export async function useSession(req, res) {
	try {
		req.session = await getSession(req)
	} catch (error) {
		console.error(error)
		serverError(res)
		return false
	}

	if (!req.session) {
		badRequest(res)
		return false
	}
	return true
}

export async function useAuth(req, res) {
	req.auth = await getAuthToken(req)
	if (!req.auth) {
		unauthorized(res)
		return false
	}
	return true
}
