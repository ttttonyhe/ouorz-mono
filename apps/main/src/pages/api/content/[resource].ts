import type { NextApiRequest, NextApiResponse } from "next"
import { z } from "zod"
import { renderMDX } from "~/content/mdx"
import { getPageById } from "~/content/pages"
import {
	getCategoryById,
	getPostById,
	getPostIds,
	getPosts,
	type GetPostsOptions,
} from "~/content/posts"
import { getSponsors } from "~/content/static-data"
import { getPathViews, getPostViews } from "~/content/views"
import { apiHandler, PUBLIC_CACHE } from "~/lib/apiHandler"
import { positiveIntegerSchema } from "~/lib/requestBody"
import { paramEquals, readParam } from "~/utilities/queryParams"

const postsQuerySchema = z.object({
	sticky: z.enum(["0", "1"]).optional(),
	categories: positiveIntegerSchema.optional(),
	categories_exclude: z
		.string()
		.max(1000)
		.regex(/^\d+(?:,\d+)*$/u)
		.optional(),
	per_page: positiveIntegerSchema.pipe(z.number().max(100)).optional(),
	page: positiveIntegerSchema.optional(),
	search: z.string().max(200).optional(),
})

const handler = async (req: NextApiRequest, res: NextApiResponse) => {
	const key = readParam(req.query.resource)
	if (!key) return res.status(404).json({ message: "Not found" })

	switch (key) {
		case "posts": {
			const parsed = postsQuerySchema.safeParse(req.query)
			if (!parsed.success)
				return res.status(400).json({ error: "Invalid query parameters" })

			const options: GetPostsOptions = {}
			if (parsed.data.sticky !== undefined) {
				options.sticky = parsed.data.sticky === "1"
			}
			if (parsed.data.categories !== undefined) {
				options.cate = parsed.data.categories
			}
			if (parsed.data.categories_exclude !== undefined) {
				options.cateExclude = parsed.data.categories_exclude
			}
			if (parsed.data.per_page !== undefined) {
				options.perPage = parsed.data.per_page
			}
			if (parsed.data.page !== undefined) {
				options.page = parsed.data.page
			}
			if (parsed.data.search !== undefined) {
				options.search = parsed.data.search
			}

			const posts = getPosts(options)
			const views = await getPostViews(posts)
			const postsWithViews = []
			for (const [index, post] of posts.entries()) {
				postsWithViews.push({
					...post,
					post_metas: {
						...post.post_metas,
						views: views[index] ?? post.post_metas.views,
					},
				})
			}

			return res.status(200).json(postsWithViews)
		}
		case "post": {
			const parsed = positiveIntegerSchema.safeParse(req.query.id)
			if (!parsed.success) return res.status(400).json({ error: "Invalid ID" })
			const id = parsed.data
			const withHtml = paramEquals(req.query.render, "html")
			const post = getPostById(id)
			if (!post) return res.status(404).json({})

			const mdxHtml =
				withHtml && !/<\w+[\s\S]*>/u.test(post.content.raw)
					? await renderMDX(post.content.raw)
					: null

			return res.status(200).json({
				...post,
				post_metas: {
					...post.post_metas,
					views:
						(await getPathViews(`/post/${post.id}`)) ?? post.post_metas.views,
				},
				mdxHtml,
			})
		}
		case "allPostIDs":
			res.setHeader("Cache-Control", PUBLIC_CACHE)
			return res.status(200).json(getPostIds())
		case "category": {
			const parsed = positiveIntegerSchema.safeParse(req.query.id)
			if (!parsed.success) return res.status(400).json({ error: "Invalid ID" })
			const category = getCategoryById(parsed.data)
			if (!category) return res.status(404).json({})
			res.setHeader("Cache-Control", PUBLIC_CACHE)
			return res.status(200).json(category)
		}
		case "sponsors":
			res.setHeader("Cache-Control", PUBLIC_CACHE)
			return res.status(200).json(getSponsors())
		case "postStats": {
			const posts = getPosts()
			const views = (await getPostViews(posts)).reduce(
				(sum, value) => sum + value,
				0
			)
			return res.status(200).json({ count: posts.length, views })
		}
		case "page": {
			const parsed = positiveIntegerSchema.safeParse(req.query.id)
			if (!parsed.success) return res.status(400).json({ error: "Invalid ID" })
			const page = getPageById(parsed.data)
			if (!page) return res.status(404).json({})

			return res.status(200).json({
				...page,
				post_metas: {
					...page.post_metas,
					views:
						(await getPathViews(`/page/${page.id}`)) ?? page.post_metas.views,
				},
			})
		}
		default:
			return res.status(404).json({ message: "Not found" })
	}
}

export default apiHandler(["GET", "HEAD"], handler)
