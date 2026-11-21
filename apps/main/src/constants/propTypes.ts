import { z } from "zod"

export const wpPostSchema = z.object({
	code: z.unknown().optional(),
	post_img: z.object({ url: z.string() }),
	post_title: z.string(),
	post_excerpt: z.object({ four: z.string() }),
	post_categories: z
		.array(z.object({ term_id: z.number().int(), name: z.string() }))
		.min(1),
	id: z.string(),
	title: z.object({ rendered: z.string() }),
	date: z.string(),
	post_metas: z.object({
		link: z.string().optional(),
		status: z.string(),
		markCount: z.number(),
		views: z.number(),
		reading: z.object({ word_count: z.number(), time_required: z.number() }),
	}),
	content: z.object({ rendered: z.string(), raw: z.string().optional() }),
})

export type WPPost = z.infer<typeof wpPostSchema>

export const githubMetricsSchema = z.object({
	followers: z.number().int().nonnegative(),
	stars: z.number().int().nonnegative(),
})

export const postMetricsSchema = z.object({
	views: z.number().int().nonnegative(),
	count: z.number().int().nonnegative(),
})

export type ListTypes = "index" | "cate" | "search"
