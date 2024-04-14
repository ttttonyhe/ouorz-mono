import { postSchema } from "@/database/schema"
import { z } from "zod"

export type PostData = Pick<z.infer<typeof postSchema>, "data">["data"]

export { default as toLocalDate } from "./toLocalDate"
