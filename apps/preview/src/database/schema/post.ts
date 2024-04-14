import { z } from "zod"

const postSchema = z.object({
	data: z
		.object({
			slug: z.number(),
			title: z.string(),
			description: z.string(),
			date: z.date(),
		})
		.passthrough(),
	content: z.string(),
})

export default postSchema
