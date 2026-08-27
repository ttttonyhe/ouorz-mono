import type { NextApiRequest } from "next"
import { z } from "zod"

/**
 * Schemas for every request body the API routes accept. Bodies arrive as
 * untrusted JSON, so each route decodes its body here before using it.
 */

export const searchBodySchema = z.object({
	query: z.string().default(""),
})

export const revalidateBodySchema = z.object({
	token: z.string(),
	path: z.string(),
})

export const summarizeBodySchema = z.object({
	identifier: z.string().min(1),
	content: z.string().min(1),
})

export const mailerBodySchema = z.object({
	fromName: z.string(),
	toEmail: z.email(),
	content: z.string(),
	url: z.url(),
})

export type MailerRequestBody = z.infer<typeof mailerBodySchema>

/** Decodes a request body, returning undefined when it does not match the schema. */
export const parseBody = <Schema extends z.ZodType>(
	schema: Schema,
	body: NextApiRequest["body"]
): z.infer<Schema> | undefined => {
	const result = schema.safeParse(body ?? {})
	return result.success ? result.data : undefined
}
