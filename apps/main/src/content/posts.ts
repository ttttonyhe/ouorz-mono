import matter from "gray-matter"
import { marked } from "marked"
import fs from "node:fs"
import path from "node:path"

export interface LocalPostFrontmatter {
	id: number
	title: string
	date: string
	categoryId: number
	categoryName: string
	excerpt: string
	image?: string
	views?: number
	sticky?: boolean
	link?: string
}

type PreNextTuple = [number, string, number]

interface PostPreNext {
	prev: PreNextTuple | []
	next: PreNextTuple | []
}

export interface LocalPost {
	code?: unknown
	post_prenext: PostPreNext
	id: string
	title: { rendered: string }
	post_title: string
	date: string
	content: { rendered: string; raw: string }
	post_excerpt: { four: string }
	post_img: { url: string }
	post_categories: { term_id: number; name: string }[]
	post_metas: {
		status: string
		markCount: number
		views: number
		reading: {
			word_count: number
			time_required: number
		}
		link?: string
	}
	sticky: boolean
}

const CONTENT_DIR = path.join(process.cwd(), "content", "posts")

const countWords = (text: string) =>
	text.trim().split(/\s+/).filter(Boolean).length

const toLocalPost = (fileName: string): LocalPost => {
	const source = fs.readFileSync(path.join(CONTENT_DIR, fileName), "utf-8")
	const { data, content } = matter(source)
	const frontmatter = data as LocalPostFrontmatter
	const wordCount = countWords(content)

	return {
		post_prenext: { prev: [], next: [] },
		id: String(frontmatter.id),
		title: { rendered: frontmatter.title },
		post_title: frontmatter.title,
		date: new Date(frontmatter.date).toISOString(),
		content: {
			rendered: marked.parse(content) as string,
			raw: content,
		},
		post_excerpt: { four: frontmatter.excerpt },
		post_img: { url: frontmatter.image ?? "" },
		post_categories: [
			{ term_id: frontmatter.categoryId, name: frontmatter.categoryName },
		],
		post_metas: {
			status: "publish",
			markCount: 0,
			views: frontmatter.views ?? 0,
			reading: {
				word_count: wordCount,
				time_required: Math.max(1, Math.ceil(wordCount / 220)),
			},
			...(frontmatter.link ? { link: frontmatter.link } : {}),
		},
		sticky: Boolean(frontmatter.sticky),
	}
}

const withPreNext = (posts: LocalPost[]): LocalPost[] =>
	posts.map((post, index) => {
		const prev = posts[index - 1]
		const next = posts[index + 1]
		return {
			...post,
			post_prenext: {
				prev: prev
					? [
							Number(prev.id),
							prev.post_title,
							prev.post_categories[0]?.term_id ?? 0,
						]
					: [],
				next: next
					? [
							Number(next.id),
							next.post_title,
							next.post_categories[0]?.term_id ?? 0,
						]
					: [],
			},
		}
	})

const getAllPostsUncached = (): LocalPost[] => {
	if (!fs.existsSync(CONTENT_DIR)) return []

	const posts = fs
		.readdirSync(CONTENT_DIR)
		.filter((fileName) => fileName.endsWith(".mdx") || fileName.endsWith(".md"))
		.map(toLocalPost)
		.sort((a, b) => +new Date(b.date) - +new Date(a.date))

	return withPreNext(posts)
}

export const getAllPosts = (): LocalPost[] => getAllPostsUncached()

interface GetPostsOptions {
	sticky?: boolean
	cate?: number
	cateExclude?: string
	perPage?: number
	search?: string
	page?: number
}

export const getPosts = (options: GetPostsOptions = {}) => {
	const { sticky, cate, cateExclude, perPage, search, page = 1 } = options
	const excluded = new Set(
		(cateExclude ?? "").split(",").filter(Boolean).map(Number)
	)

	let posts = getAllPosts().filter((post) => {
		const cid = post.post_categories[0]?.term_id
		if (excluded.has(cid)) return false
		if (typeof sticky === "boolean" && post.sticky !== sticky) return false
		if (cate && cid !== cate) return false
		if (search) {
			const q = search.toLowerCase()
			return (
				post.post_title.toLowerCase().includes(q) ||
				post.post_excerpt.four.toLowerCase().includes(q)
			)
		}
		return true
	})

	if (perPage) {
		const start = (Math.max(1, page) - 1) * perPage
		posts = posts.slice(start, start + perPage)
	}

	return posts
}

export const getPostById = (id: number) =>
	getAllPosts().find((post) => Number(post.id) === id)

export const getPostIds = () => getAllPosts().map((post) => Number(post.id))

export const getCategoryById = (id: number) => {
	const posts = getAllPosts().filter(
		(post) => post.post_categories[0]?.term_id === id
	)
	if (posts.length === 0) return null
	return {
		id,
		name: posts[0].post_categories[0].name,
		count: posts.length,
	}
}

export const getPostStats = () => {
	const posts = getAllPosts()
	return {
		count: posts.length,
		views: posts.reduce((sum, post) => sum + post.post_metas.views, 0),
	}
}
