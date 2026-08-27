import { z } from "zod"

const postSchema = z.object({
	data: z.looseObject({
		slug: z.number(),
		title: z.string(),
		description: z.string(),
		date: z.date(),
	}),
	content: z.string(),
})

export default postSchema
