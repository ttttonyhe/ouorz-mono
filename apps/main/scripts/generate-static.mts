import { execFileSync } from "node:child_process"
import fs from "node:fs"
import path from "node:path"

import matter from "gray-matter"
import { marked } from "marked"
import type { z } from "zod"

import {
	renderLlmsTxt,
	renderPagesMarkdown,
	renderProfileMarkdown,
} from "../src/content/markdown.ts"
import {
	ALL_PUBLICATIONS,
	PREPRINTS,
	PROFILE_DESCRIPTION,
	PROFILE_UPDATED_AT,
	SITE_URL,
	STATIC_ROUTES,
} from "../src/content/profile.ts"
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

interface SitemapEntry {
	path: string
	lastmod?: string
}

interface RssItem {
	title: string
	description: string
	link: string
	guid: string
	author: string
	categories: string[]
	date: Date
	contentHtml?: string
}

const PUBLIC_DIR = path.join(process.cwd(), "public")

const isShallowRepo = (() => {
	try {
		return (
			execFileSync("git", ["rev-parse", "--is-shallow-repository"], {
				cwd: process.cwd(),
				encoding: "utf8",
				stdio: ["ignore", "pipe", "ignore"],
			}).trim() === "true"
		)
	} catch {
		return true
	}
})()

const sanitizeStr = (str: string) =>
	str.replaceAll(
		// oxlint-disable-next-line eslint/no-control-regex -- XML 1.0 excludes these control characters.
		/[\u0000-\u0008\u000B\u000C\u000E-\u001F\u007F-\u0084\u0086-\u009F\uD800-\uDFFF\uFDD0-\uFDFF\uFFFF]/gu,
		""
	)

const xmlEscape = (value: string) =>
	sanitizeStr(value)
		.replaceAll("&", "&amp;")
		.replaceAll("<", "&lt;")
		.replaceAll(">", "&gt;")
		.replaceAll('"', "&quot;")
		.replaceAll("'", "&apos;")

const wrapCdata = (value: string) =>
	`<![CDATA[${sanitizeStr(value).replaceAll("]]>", "]]]]><![CDATA[>")}]]>`

const getGitLastMod = (sources: readonly string[]): string | null => {
	if (isShallowRepo) return null
	try {
		const iso = execFileSync(
			"git",
			["log", "-1", "--format=%cI", "--", ...sources],
			{
				cwd: process.cwd(),
				encoding: "utf8",
				stdio: ["ignore", "pipe", "ignore"],
			}
		).trim()
		return iso ? iso.slice(0, 10) : null
	} catch {
		return null
	}
}

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

const renderRssItem = (item: RssItem) => {
	const categories = item.categories
		.map((cat) => `      <category>${wrapCdata(cat)}</category>`)
		.join("\n")
	const contentEncoded = item.contentHtml
		? `\n      <content:encoded>${wrapCdata(item.contentHtml)}</content:encoded>`
		: ""
	return `    <item>
      <title>${wrapCdata(item.title)}</title>
      <description>${wrapCdata(item.description)}</description>
      <link>${xmlEscape(item.link)}</link>
      <guid isPermaLink="true">${xmlEscape(item.guid)}</guid>
${categories}
      <dc:creator>${wrapCdata(item.author)}</dc:creator>
      <pubDate>${item.date.toUTCString()}</pubDate>${contentEncoded}
    </item>`
}

const generateRSS = (posts: FeedPost[]) => {
	const items: RssItem[] = posts.map((post) => {
		const url = `${SITE_URL}/post/${post.id}`
		return {
			title: post.title,
			description: post.excerpt,
			link: url,
			guid: url,
			author: "Tony (Lipeng) He",
			categories: [post.categoryName],
			date: post.date,
			contentHtml: marked.parse(post.content, { async: false }),
		}
	})

	const datedResearch = [...PREPRINTS, ...ALL_PUBLICATIONS]
		.filter((paper) => Boolean(paper.datePublished))
		.toSorted((a, b) =>
			(b.datePublished ?? "").localeCompare(a.datePublished ?? "")
		)

	for (const paper of datedResearch) {
		const url = paper.canonicalUrl ?? paper.links[0]?.href ?? `${SITE_URL}/`
		const venueLabel = paper.venue?.name ?? "Preprint"
		items.push({
			title: paper.title,
			description: `${paper.authors}. ${venueLabel}.`,
			link: url,
			guid: url,
			author: paper.authors,
			categories: [venueLabel],
			date: new Date(`${paper.datePublished}T00:00:00Z`),
		})
	}

	const feedUrl = `${SITE_URL}/feed.xml`
	const title = "Tony (Lipeng) He | 贺莉朋"
	const renderedItems = items.map((item) => renderRssItem(item)).join("\n")

	return `<?xml version="1.0" encoding="UTF-8"?>
<rss xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom" version="2.0">
  <channel>
    <title>${wrapCdata(title)}</title>
    <description>${wrapCdata(PROFILE_DESCRIPTION)}</description>
    <link>${xmlEscape(SITE_URL)}</link>
    <image>
      <url>${xmlEscape(`${SITE_URL}/tony.png`)}</url>
      <title>${xmlEscape(title)}</title>
      <link>${xmlEscape(SITE_URL)}</link>
    </image>
    <generator>Next.js</generator>
    <lastBuildDate>${new Date().toUTCString()}</lastBuildDate>
    <atom:link href="${xmlEscape(feedUrl)}" rel="self" type="application/rss+xml"/>
    <copyright>${wrapCdata(`© ${new Date().getFullYear()} Tony (Lipeng) He`)}</copyright>
    <language>en</language>
    <docs>https://www.rssboard.org/rss-specification</docs>
${renderedItems}
  </channel>
</rss>
`
}

const generateSitemap = (posts: FeedPost[], pages: LocalPageFrontmatter[]) => {
	const staticEntries: SitemapEntry[] = STATIC_ROUTES.filter(
		(route) => route.sitemap
	).map(({ path: route, sources }) => {
		const entry: SitemapEntry = { path: route }
		const lastmod = sources ? getGitLastMod(sources) : PROFILE_UPDATED_AT
		if (lastmod) entry.lastmod = lastmod
		return entry
	})

	const categories = [...new Set(posts.map((post) => post.categoryId))]
	const categoryEntries: SitemapEntry[] = categories.map((id) => {
		const entry: SitemapEntry = { path: `/cate/${id}` }
		const lastmod = posts
			.find((post) => post.categoryId === id)
			?.date.toISOString()
			.slice(0, 10)
		if (lastmod) entry.lastmod = lastmod
		return entry
	})

	const entries: SitemapEntry[] = [
		...staticEntries,
		...posts.map((post) => ({
			path: `/post/${post.id}`,
			lastmod: post.date.toISOString().slice(0, 10),
		})),
		...pages.map((page) => ({
			path: `/page/${page.id}`,
			lastmod: page.date.toISOString().slice(0, 10),
		})),
		...categoryEntries,
	]

	const urls = entries
		.map(({ path: route, lastmod }) => {
			const lastmodLine = lastmod ? `\n    <lastmod>${lastmod}</lastmod>` : ""
			return `  <url>\n    <loc>${xmlEscape(`${SITE_URL}${route}`)}</loc>${lastmodLine}\n  </url>`
		})
		.join("\n")

	return `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${urls}
</urlset>
`
}

const records = readContent("posts", postFrontmatterSchema)
const posts = records.filter(
	(post) => !post.noindex && isArticleContent(post.content, post.link)
)
const pages = readContent("pages", pageFrontmatterSchema).filter(
	(page) => !page.noindex && isArticleContent(page.content)
)

const artifacts: readonly [string, string][] = [
	["feed.xml", generateRSS(posts)],
	["sitemap.xml", generateSitemap(posts, pages)],
	["llms.txt", renderLlmsTxt(posts)],
	["index.md", renderProfileMarkdown()],
	["pages.md", renderPagesMarkdown()],
]

for (const [name, content] of artifacts) {
	fs.writeFileSync(path.join(PUBLIC_DIR, name), content, "utf8")
	console.log(`Generated public/${name}`)
}
