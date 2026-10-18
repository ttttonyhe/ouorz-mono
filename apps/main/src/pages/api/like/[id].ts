import { z } from "zod"

import { getPostById } from "~/content/posts"
import { apiHandler } from "~/lib/apiHandler"

const postIdSchema = z
	.string()
	.regex(/^[1-9]\d*$/u)
	.transform(Number)
	.pipe(z.number().int().positive())

export default apiHandler(["POST"], (req, res) => {
	const id = postIdSchema.safeParse(req.query.id)
	if (!id.success) return res.status(400).json({ error: "Invalid post ID" })
	if (!getPostById(id.data))
		return res.status(404).json({ error: "Post not found" })
	return res.status(501).json({ error: "Likes are not configured" })
})

export const config = { api: { bodyParser: false } }
