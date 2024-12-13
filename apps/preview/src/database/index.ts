import { postSchema } from "@/database/schema"
import { z } from "zod"

export type RawPostData = Pick<z.infer<typeof postSchema>, "data">["data"]

export interface Post {
	slug: string
	path: string
	data: {
		meta: RawPostData
		source: string
	}
}
