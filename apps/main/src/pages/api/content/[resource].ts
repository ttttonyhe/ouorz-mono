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

const handler = async (req: NextApiRequest, res: NextApiResponse) => {
	const { resource } = req.query
	const key = Array.isArray(resource) ? resource[0] : resource

	switch (key) {
		case "posts": {
			res.setHeader("Cache-Control", "no-store, max-age=0")

			const sticky =
				typeof req.query.sticky === "string"
					? req.query.sticky === "1"
					: undefined
			const cate =
				typeof req.query.categories === "string"
					? Number(req.query.categories)
					: undefined
			const cateExclude =
				typeof req.query.categories_exclude === "string"
					? req.query.categories_exclude
					: undefined
			const perPage =
				typeof req.query.per_page === "string"
					? Number(req.query.per_page)
					: undefined
			const page =
				typeof req.query.page === "string" ? Number(req.query.page) : 1
			const search =
				typeof req.query.search === "string" ? req.query.search : undefined

			const posts = getPosts({
				sticky,
				cate,
				cateExclude,
				perPage,
				page,
				search,
			})

			const postsWithViews = await Promise.all(
				posts.map(async (post) => ({
					...post,
					post_metas: {
						...post.post_metas,
						views: await getPathViews(`/post/${post.id}`),
					},
				}))
			)

			return res.status(200).json(postsWithViews)
		}
		case "post": {
			res.setHeader("Cache-Control", "no-store, max-age=0")

			const id = Number(req.query.id)
			const withMdx =
				typeof req.query.render === "string" && req.query.render === "mdx"
			const post = getPostById(id)
			if (!post) return res.status(404).json({})

			let mdxSource = null
			if (withMdx && !/<\w+[\s\S]*>/.test(post.content.raw)) {
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
				...(mdxSource ? { mdxSource } : {}),
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
