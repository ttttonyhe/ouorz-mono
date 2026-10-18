import fs from "node:fs"
import path from "node:path"

import matter from "gray-matter"
import { marked } from "marked"

import { pageFrontmatterSchema } from "~/content/schema"
import { getContentLanguage, type ContentLanguage } from "~/content/text"

export interface LocalPage {
	id: string
	title: { rendered: string }
	date: string
	language: ContentLanguage
	noindex: boolean
	content: { rendered: string }
	post_metas: { views: number }
}

const CONTENT_DIR = path.join(process.cwd(), "content", "pages")

const toLocalPage = (fileName: string): LocalPage => {
	const source = fs.readFileSync(path.join(CONTENT_DIR, fileName), "utf-8")
	const { data, content } = matter(source)
	const frontmatter = pageFrontmatterSchema.parse(data)

	return {
		id: String(frontmatter.id),
		title: { rendered: frontmatter.title },
		date: frontmatter.date.toISOString(),
		noindex: frontmatter.noindex,
		language: getContentLanguage(
			`${frontmatter.title} ${content}`,
			frontmatter.language
		),
		content: { rendered: marked.parse(content, { async: false }) },
		post_metas: { views: frontmatter.views ?? 0 },
	}
}

export const getAllPages = (): LocalPage[] => {
	if (!fs.existsSync(CONTENT_DIR)) return []

	return fs
		.readdirSync(CONTENT_DIR)
		.filter((fileName) => fileName.endsWith(".mdx") || fileName.endsWith(".md"))
		.map((fileName) => toLocalPage(fileName))
		.filter((page) => page.content.rendered.trim().length > 0)
		.toSorted((a, b) => +new Date(b.date) - +new Date(a.date))
}

export const getPageById = (id: number) =>
	getAllPages().find((page) => Number(page.id) === id)

export const getPageIds = () => getAllPages().map((page) => Number(page.id))
