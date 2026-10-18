import type { NextApiRequest } from "next"
import { z } from "zod"

export const searchBodySchema = z.object({
	query: z.string().max(200).default(""),
})

export const revalidateBodySchema = z.object({
	token: z.string().min(1).max(512),
	path: z
		.string()
		.max(2048)
		.regex(/^\/(?!\/)[^?#\\\s]*$/u),
})

export const summarizeBodySchema = z.strictObject({
	identifier: z.string().regex(/^posts\/[1-9]\d{0,9}$/u),
})

export const mailerBodySchema = z.object({
	fromName: z
		.string()
		.trim()
		.min(1)
		.max(100)
		.regex(/^[^\r\n]*$/u),
	toEmail: z.email(),
	content: z.string().trim().min(1).max(10_000),
	url: z.url({ protocol: /^https$/u }).max(2048),
})

export type MailerRequestBody = z.infer<typeof mailerBodySchema>

export const parseBody = <Schema extends z.ZodType>(
	schema: Schema,
	req: Pick<NextApiRequest, "body">
): z.infer<Schema> | undefined => {
	const result = schema.safeParse(req.body ?? {})
	return result.success ? result.data : undefined
}
