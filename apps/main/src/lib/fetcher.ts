import type { z } from "zod"

const createFetcher = <Data>(schema: z.ZodType<Data>, init?: RequestInit) => {
	return async (route: string): Promise<Data> => {
		const response = await fetch(route, init)
		if (!response.ok) throw new Error(`Request failed: ${response.status}`)
		const data: unknown = await response.json()
		return schema.parse(data)
	}
}

export default createFetcher
