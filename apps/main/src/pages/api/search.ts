import type { NextApiRequest, NextApiResponse } from "next"
import { getAllPosts, type LocalPost } from "~/content/posts"

const RESULT_LIMIT = 10
const EXCLUDED_CATEGORY_IDS = new Set([2, 5, 74, 334, 335])

type SearchHit = {
	post_id: number
	post_title: string
	post_excerpt: string
}

type SearchResponse = {
	hits: SearchHit[]
	nbHits: number
	query: string
	page: number
	hitsPerPage: number
}

const normalizeText = (value: string) => value.toLowerCase().trim()

const stripHtml = (value: string) =>
	value
		.replace(/<[^>]*>/g, " ")
		.replace(/\s+/g, " ")
		.trim()

const scorePost = (
	post: LocalPost,
	normalizedQuery: string,
	queryTokens: string[]
) => {
	if (!normalizedQuery) return 1

	const title = normalizeText(post.post_title)
	const excerpt = normalizeText(post.post_excerpt.four ?? "")
	const content = normalizeText(stripHtml(post.content.raw ?? ""))

	let score = 0
	if (title === normalizedQuery) score += 500
	if (title.startsWith(normalizedQuery)) score += 240
	if (title.includes(normalizedQuery)) score += 180
	if (excerpt.includes(normalizedQuery)) score += 90
	if (content.includes(normalizedQuery)) score += 45

	queryTokens.forEach((token) => {
		if (title.includes(token)) score += 30
		if (excerpt.includes(token)) score += 15
		if (content.includes(token)) score += 8
	})

	return score
}

const search = async (
	req: NextApiRequest,
	res: NextApiResponse<SearchResponse>
) => {
	const { query } = req.body ?? {}
	const searchQuery = typeof query === "string" ? query : ""
	const normalizedQuery = normalizeText(searchQuery)
	const queryTokens = normalizedQuery.split(/\s+/).filter(Boolean)

	const rankedHits = getAllPosts()
		.filter((post) => {
			const categoryId = post.post_categories[0]?.term_id
			return !EXCLUDED_CATEGORY_IDS.has(categoryId)
		})
		.map((post) => ({
			post,
			score: scorePost(post, normalizedQuery, queryTokens),
		}))
		.filter(({ score }) => (normalizedQuery ? score > 0 : true))
		.sort(
			(a, b) =>
				b.score - a.score || +new Date(b.post.date) - +new Date(a.post.date)
		)

	const hits = rankedHits.slice(0, RESULT_LIMIT).map(({ post }) => ({
		post_id: Number(post.id),
		post_title: post.post_title,
		post_excerpt: post.post_excerpt.four,
	}))

	res.setHeader("Cache-Control", "no-store, max-age=0")

	return res.status(200).json({
		hits,
		nbHits: rankedHits.length,
		query: searchQuery,
		page: 0,
		hitsPerPage: RESULT_LIMIT,
	})
}

export default search
