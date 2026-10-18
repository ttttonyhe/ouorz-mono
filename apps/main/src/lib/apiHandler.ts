import type { NextApiHandler } from "next"
import { z } from "zod"

export const PUBLIC_CACHE = "public, s-maxage=1200, stale-while-revalidate=600"

export class ApiError extends Error {
	constructor(
		readonly status: number,
		message: string
	) {
		super(message)
		this.name = "ApiError"
	}
}

export const apiHandler =
	(methods: readonly string[], handler: NextApiHandler): NextApiHandler =>
	async (req, res) => {
		res.setHeader("Cache-Control", "no-store")
		if (!methods.includes(req.method ?? "")) {
			res.setHeader("Allow", methods.join(", "))
			return res.status(405).json({ error: "Method not allowed" })
		}

		try {
			return await handler(req, res)
		} catch (error) {
			res.setHeader("Cache-Control", "no-store")
			const status = error instanceof ApiError ? error.status : 500
			const message =
				error instanceof ApiError ? error.message : "Internal server error"
			return res.status(status).json({ error: message })
		}
	}

export const requireEnv = (name: string): string => {
	const value = process.env[name]?.trim()
	if (!value) throw new ApiError(503, "Service is not configured")
	return value
}

export const fetchJSON = async <Schema extends z.ZodType>(
	schema: Schema,
	url: string | URL,
	init: RequestInit = {}
): Promise<z.infer<Schema>> => {
	try {
		const response = await fetch(url, {
			...init,
			redirect: "error",
			signal: init.signal ?? AbortSignal.timeout(10_000),
		})
		if (!response.ok) {
			await response.body?.cancel()
			throw new Error("Upstream request failed")
		}
		return schema.parse(await response.json())
	} catch {
		throw new ApiError(502, "Upstream service unavailable")
	}
}
