import fs from "node:fs"
import path from "node:path"

import matter from "gray-matter"
import { marked } from "marked"
import RSS from "rss"
import stripAnsi from "strip-ansi"
import type { z } from "zod"

import {
	pageFrontmatterSchema,
	postFrontmatterSchema,
} from "../src/content/schema.ts"
import type {
	LocalPageFrontmatter,
	LocalPostFrontmatter,
} from "../src/content/schema.ts"
import { isArticleContent } from "../src/content/text.ts"

type FeedPost = LocalPostFrontmatter & { content: string }

const SITE_URL = "https://lipeng.ac"
const PUBLIC_DIR = path.join(process.cwd(), "public")
const STATIC_PATHS = [
	"/",
	"/pages",
	"/links",
	"/podcasts",
	"/reading-list",
	"/sponsor",
	"/web3",
]

const sanitizeStr = (str: string) =>
	stripAnsi(
		str.replaceAll(
			// oxlint-disable-next-line eslint/no-control-regex -- XML 1.0 excludes these control characters.
			/[\u0000-\u0008\u000B\u000C\u000E-\u001F\u007F-\u0084\u0086-\u009F\uD800-\uDFFF\uFDD0-\uFDFF\uFFFF]/gu,
			""
		)
	)

const xmlEscape = (value: string) =>
	sanitizeStr(value)
		.replaceAll("&", "&amp;")
		.replaceAll("<", "&lt;")
		.replaceAll(">", "&gt;")
		.replaceAll('"', "&quot;")
		.replaceAll("'", "&apos;")

const readContent = <T extends LocalPageFrontmatter>(
	directory: string,
	schema: z.ZodType<T>
) => {
	const contentDir = path.join(process.cwd(), "content", directory)
	const records = fs
		.readdirSync(contentDir)
		.filter((file) => /\.mdx?$/u.test(file))
		.map((file) => {
			const { data, content } = matter(
				fs.readFileSync(path.join(contentDir, file), "utf8")
			)
			return Object.assign(schema.parse(data), { content })
		})
		.toSorted((a, b) => b.date.getTime() - a.date.getTime())

	if (new Set(records.map((record) => record.id)).size !== records.length) {
		throw new Error(`Duplicate content IDs in ${contentDir}`)
	}

	return records
}

const generateRSS = (posts: FeedPost[]) => {
	const feed = new RSS({
		title: "Tony (Lipeng) He | 贺莉朋",
		language: "zh-CN",
		generator: "Next.js",
		site_url: SITE_URL,
		feed_url: `${SITE_URL}/feed.xml`,
		image_url: `${SITE_URL}/tony.png`,
		docs: "https://www.rssboard.org/rss-specification",
		copyright: `© ${new Date().getFullYear()} Tony He`,
		description:
			"Articles by Tony (Lipeng) He, 贺莉朋. Visit lipeng.ac for the current academic profile and research.",
	})

	for (const post of posts) {
		feed.item({
			url: `${SITE_URL}/post/${post.id}`,
			author: "Tony He",
			title: sanitizeStr(post.title),
			categories: [post.categoryName],
			date: post.date,
			description: sanitizeStr(post.excerpt),
			custom_elements: [
				{
					"content:encoded": {
						_cdata: sanitizeStr(marked.parse(post.content, { async: false })),
					},
				},
			],
		})
	}

	return feed.xml({ indent: true })
}

const generateSitemap = (posts: FeedPost[], pages: LocalPageFrontmatter[]) => {
	const categories = [...new Set(posts.map((post) => post.categoryId))]
	const paths = [
		...STATIC_PATHS,
		...posts.map((post) => `/post/${post.id}`),
		...pages.map((page) => `/page/${page.id}`),
		...categories.map((id) => `/cate/${id}`),
	]
	const urls = paths
		.map(
			(route) => `  <url>
    <loc>${xmlEscape(`${SITE_URL}${route}`)}</loc>
  </url>`
		)
		.join("\n")

	return `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${urls}
</urlset>
`
}

const generateLLMsTxt = (posts: FeedPost[]) => {
	const articleLinks =
		posts.length > 0
			? `\n## Articles\n\n${posts.map((post) => `- [${post.title.replaceAll(/[[\]\r\n]/gu, " ")}](${SITE_URL}/post/${post.id}): ${post.date.toISOString().slice(0, 10)}`).join("\n")}\n`
			: ""

	return `# Tony (Lipeng) He | 贺莉朋

> Personal academic website of Tony (Lipeng) He, 贺莉朋, a computer science PhD student at the University of Waterloo.

The homepage contains the current biography, research interests, publications, projects, and contact information. Older blog entries are historical records and may contain outdated affiliations and project details.

Tony publishes as Lipeng He. His research studies the safety and security of large language models and AI agents when models are adapted or deployed, drawing on machine learning, systems security, and applied cryptography. He is advised by N. Asokan and Yaoliang Yu and builds research tools and AI security software at Retrograde Labs.

贺莉朋也使用 Tony He 这一名字，论文署名为 Lipeng He。他在滑铁卢大学攻读计算机科学博士，研究大语言模型与 AI 智能体的安全，结合机器学习、系统安全与应用密码学开展研究。

## Academic profile

- [Academic profile](${SITE_URL}/): Current biography, research, publications, and contact links.
- [Google Scholar](https://scholar.google.com/citations?user=6yFlE_sAAAAJ): Publication record.
- [GitHub](https://github.com/ttttonyhe): Source code and software projects.
- [ORCID](https://orcid.org/0009-0002-1802-7394): Researcher identifier.
- [X](https://x.com/ttttonyhe): Public posts.
- [YouTube](https://www.youtube.com/@ttttonyhe): YouTube channel.
${articleLinks}
## Other pages

- [Pages](${SITE_URL}/pages): Site directory.
- [Links](${SITE_URL}/links): Tools and independent websites.
- [Podcasts](${SITE_URL}/podcasts): Podcasts recommended by Tony.
- [Reading list](${SITE_URL}/reading-list): Books from Tony's public Goodreads shelves.
- [RSS feed](${SITE_URL}/feed.xml): Existing site updates and links.

## Optional

- [Sitemap](${SITE_URL}/sitemap.xml): Indexable site pages.
- [Sponsor](${SITE_URL}/sponsor): Support this site.
- [Web3](${SITE_URL}/web3): Public wallets and identities.
`
}

const records = readContent("posts", postFrontmatterSchema)
const posts = records.filter(
	(post) => !post.noindex && isArticleContent(post.content, post.link)
)
const pages = readContent("pages", pageFrontmatterSchema).filter(
	(page) => !page.noindex && isArticleContent(page.content)
)

for (const [name, content] of [
	["feed.xml", generateRSS(records)],
	["sitemap.xml", generateSitemap(posts, pages)],
	["llms.txt", generateLLMsTxt(posts)],
]) {
	fs.writeFileSync(path.join(PUBLIC_DIR, name), content, "utf8")
	console.log(`Generated public/${name}`)
}
