import matter from "gray-matter"
import { marked } from "marked"
import fs from "node:fs"
import path from "node:path"

interface LocalPageFrontmatter {
	id: number
	title: string
	date: string
	views?: number
}

export interface LocalPage {
	id: string
	title: { rendered: string }
	date: string
	content: { rendered: string }
	post_metas: { views: number }
}

const CONTENT_DIR = path.join(process.cwd(), "content", "pages")

const toLocalPage = (fileName: string): LocalPage => {
	const source = fs.readFileSync(path.join(CONTENT_DIR, fileName), "utf-8")
	const { data, content } = matter(source)
	const frontmatter = data as LocalPageFrontmatter

	return {
		id: String(frontmatter.id),
		title: { rendered: frontmatter.title },
		date: new Date(frontmatter.date).toISOString(),
		content: { rendered: marked.parse(content) as string },
		post_metas: { views: frontmatter.views ?? 0 },
	}
}

export const getAllPages = (): LocalPage[] => {
	if (!fs.existsSync(CONTENT_DIR)) return []

	return fs
		.readdirSync(CONTENT_DIR)
		.filter((fileName) => fileName.endsWith(".mdx") || fileName.endsWith(".md"))
		.map(toLocalPage)
		.sort((a, b) => +new Date(b.date) - +new Date(a.date))
}

export const getPageById = (id: number) =>
	getAllPages().find((page) => Number(page.id) === id)

export const getPageIds = () => getAllPages().map((page) => Number(page.id))
