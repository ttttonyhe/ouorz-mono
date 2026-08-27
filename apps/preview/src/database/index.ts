import { z } from "zod"

import { postSchema } from "@/database/schema"

export type RawPostData = Pick<z.infer<typeof postSchema>, "data">["data"]

export interface Post {
	slug: string
	path: string
	data: {
		meta: RawPostData
		source: string
	}
}
