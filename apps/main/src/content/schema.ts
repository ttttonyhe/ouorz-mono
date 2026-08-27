import { z } from "zod"

/**
 * Schemas for the local content files. Frontmatter and JSON data are read from
 * disk at build time, so each file is decoded here before the rest of the app
 * relies on its shape.
 */

export const pageFrontmatterSchema = z.object({
	id: z.number(),
	title: z.string(),
	date: z.coerce.date(),
	views: z.number().optional(),
})

export const postFrontmatterSchema = z.object({
	id: z.number(),
	title: z.string(),
	date: z.coerce.date(),
	categoryId: z.number(),
	categoryName: z.string(),
	excerpt: z.string(),
	image: z.string().optional(),
	views: z.number().optional(),
	sticky: z.boolean().optional(),
	link: z.string().optional(),
})

export const sponsorsSchema = z.object({
	donors: z.array(
		z.object({
			name: z.string(),
			date: z.string(),
			unit: z.string(),
			amount: z.union([z.string(), z.number()]),
		})
	),
})

export type LocalPageFrontmatter = z.infer<typeof pageFrontmatterSchema>
export type LocalPostFrontmatter = z.infer<typeof postFrontmatterSchema>
export type SponsorsData = z.infer<typeof sponsorsSchema>
