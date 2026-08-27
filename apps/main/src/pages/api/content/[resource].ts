import type { NextApiRequest, NextApiResponse } from "next"

import { serializeMDX } from "~/content/mdx"
import { getPageById } from "~/content/pages"
import {
	getCategoryById,
	getPostById,
	getPostIds,
	getPosts,
	getPostStats,
} from "~/content/posts"
import { getSponsors } from "~/content/static-data"
import { getPathViews } from "~/content/views"
import {
	paramEquals,
	readNumberParam,
	readParam,
} from "~/utilities/queryParams"

const handler = async (req: NextApiRequest, res: NextApiResponse) => {
	const key = readParam(req.query.resource)

	switch (key) {
		case "posts": {
			res.setHeader("Cache-Control", "no-store, max-age=0")

			const stickyParam = readParam(req.query.sticky)
			const sticky = stickyParam === undefined ? undefined : stickyParam === "1"
			const cate = readNumberParam(req.query.categories)
			const cateExclude = readParam(req.query.categories_exclude)
			const perPage = readNumberParam(req.query.per_page)
			const page = readNumberParam(req.query.page) ?? 1
			const search = readParam(req.query.search)

			const posts = getPosts({
				sticky,
				cate,
				cateExclude,
				perPage,
				page,
				search,
			})

			const views = await Promise.all(
				posts.map((post) => getPathViews(`/post/${post.id}`))
			)
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

			const id = Number(req.query.id)
			const withMdx = paramEquals(req.query.render, "mdx")
			const post = getPostById(id)
			if (!post) return res.status(404).json({})

			let mdxSource = null
			if (withMdx && !/<\w+[\s\S]*>/u.test(post.content.raw)) {
				try {
					mdxSource = await serializeMDX(post.content.raw)
				} catch {
					mdxSource = null
				}
			}

			return res.status(200).json({
				...post,
				post_metas: {
					...post.post_metas,
					views: await getPathViews(`/post/${post.id}`),
				},
				mdxSource,
			})
		}
		case "allPostIDs":
			return res.status(200).json(getPostIds())
		case "category": {
			const id = Number(req.query.id)
			const category = getCategoryById(id)
			if (!category) return res.status(404).json({})
			return res.status(200).json(category)
		}
		case "sponsors":
			return res.status(200).json(getSponsors())
		case "postStats": {
			res.setHeader("Cache-Control", "no-store, max-age=0")

			const stats = getPostStats()
			const posts = getPosts()
			const views = (
				await Promise.all(posts.map((post) => getPathViews(`/post/${post.id}`)))
			).reduce((sum, value) => sum + value, 0)
			return res.status(200).json({ ...stats, views })
		}
		case "page": {
			res.setHeader("Cache-Control", "no-store, max-age=0")

			const id = Number(req.query.id)
			const page = getPageById(id)
			if (!page) return res.status(404).json({})

			return res.status(200).json({
				...page,
				post_metas: {
					...page.post_metas,
					views: await getPathViews(`/page/${page.id}`),
				},
			})
		}
		default:
			return res.status(404).json({ message: "Not found" })
	}
}

export default handler
