import type { NextApiRequest, NextApiResponse } from "next"
import { z } from "zod"

import { renderMDX } from "~/content/mdx"
import { getPageById } from "~/content/pages"
import {
	getCategoryById,
	getPostById,
	getPostIds,
	getPosts,
} from "~/content/posts"
import { getSponsors } from "~/content/static-data"
import { getPathViews, getPostViews } from "~/content/views"
import { apiHandler } from "~/lib/apiHandler"
import { paramEquals, readParam } from "~/utilities/queryParams"

const positiveInteger = z
	.string()
	.regex(/^[1-9]\d*$/u)
	.transform(Number)
	.pipe(z.number().int().positive())
const postsQuerySchema = z.object({
	sticky: z.enum(["0", "1"]).optional(),
	categories: positiveInteger.optional(),
	categories_exclude: z
		.string()
		.max(1000)
		.regex(/^\d+(?:,\d+)*$/u)
		.optional(),
	per_page: positiveInteger.pipe(z.number().max(100)).optional(),
	page: positiveInteger.optional(),
	search: z.string().max(200).optional(),
})

const handler = async (req: NextApiRequest, res: NextApiResponse) => {
	const key = readParam(req.query.resource)
	if (!key) return res.status(404).json({ message: "Not found" })

	switch (key) {
		case "posts": {
			res.setHeader("Cache-Control", "no-store, max-age=0")

			const parsed = postsQuerySchema.safeParse(req.query)
			if (!parsed.success)
				return res.status(400).json({ error: "Invalid query parameters" })
			const stickyParam = parsed.data.sticky
			const sticky = stickyParam === undefined ? undefined : stickyParam === "1"
			const {
				categories: cate,
				categories_exclude: cateExclude,
				per_page: perPage,
				page,
				search,
			} = parsed.data

			const posts = getPosts({
				sticky,
				cate,
				cateExclude,
				perPage,
				page,
				search,
			})

			const views = await getPostViews(posts)
			const postsWithViews = posts.map((post, index) =>
				Object.assign({}, post, {
					post_metas: Object.assign({}, post.post_metas, {
						views: views[index],
					}),
				})
			)

			return res.status(200).json(postsWithViews)
		}
		case "post": {
			res.setHeader("Cache-Control", "no-store, max-age=0")

			const parsed = positiveInteger.safeParse(req.query.id)
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
			return res.status(200).json(getPostIds())
		case "category": {
			const parsed = positiveInteger.safeParse(req.query.id)
			if (!parsed.success) return res.status(400).json({ error: "Invalid ID" })
			const id = parsed.data
			const category = getCategoryById(id)
			if (!category) return res.status(404).json({})
			return res.status(200).json(category)
		}
		case "sponsors":
			return res.status(200).json(getSponsors())
		case "postStats": {
			res.setHeader("Cache-Control", "no-store, max-age=0")

			const posts = getPosts()
			const views = (await getPostViews(posts)).reduce(
				(sum, value) => sum + value,
				0
			)
			return res.status(200).json({ count: posts.length, views })
		}
		case "page": {
			res.setHeader("Cache-Control", "no-store, max-age=0")

			const parsed = positiveInteger.safeParse(req.query.id)
			if (!parsed.success) return res.status(400).json({ error: "Invalid ID" })
			const id = parsed.data
			const page = getPageById(id)
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
