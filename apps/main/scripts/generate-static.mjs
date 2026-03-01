import matter from "gray-matter"
import { marked } from "marked"
import fs from "node:fs"
import path from "node:path"
import RSS from "rss"
import stripAnsi from "strip-ansi"

const CONTENT_DIR = path.join(process.cwd(), "content", "posts")
const PUBLIC_DIR = path.join(process.cwd(), "public")

const Categories = ["personal", "technology", "life", "blogs"]

const sanitizeStr = (str) =>
	stripAnsi(
		str.replace(
			/[\u0000-\u0008\u000B\u000C\u000E-\u001F\u007f-\u0084\u0086-\u009f\uD800-\uDFFF\uFDD0-\uFDFF\uFFFF\uC008]/g,
			""
		)
	)

const getPosts = () => {
	if (!fs.existsSync(CONTENT_DIR)) return []

	return fs
		.readdirSync(CONTENT_DIR)
		.filter((f) => f.endsWith(".mdx") || f.endsWith(".md"))
		.map((fileName) => {
			const source = fs.readFileSync(path.join(CONTENT_DIR, fileName), "utf-8")
			const { data, content } = matter(source)
			return {
				id: data.id,
				date: new Date(data.date).toISOString(),
				title: data.title,
				excerpt: data.excerpt,
				content: marked.parse(content),
				image: data.image ?? null,
			}
		})
		.sort((a, b) => +new Date(b.date) - +new Date(a.date))
}

/* ── RSS Feed ──────────────────────────────────────────────── */

const generateRSS = (posts) => {
	const feed = new RSS({
		title: "Tony He",
		language: "zh-cn",
		categories: Categories,
		generator: "Next.js",
		site_url: "https://lipeng.ac",
		feed_url: "https://lipeng.ac/feed",
		image_url: "https://lipeng.ac/tony.png",
		webMaster: "tony.hlp@hotmail.com (Tony He)",
		managingEditor: "tony.hlp@hotmail.com (Tony He)",
		docs: "https://www.rssboard.org/rss-specification",
		copyright: `© ${new Date().getFullYear()} Tony He`,
		description: "Tony (Lipeng) He is a researcher and software engineer.",
	})

	for (const post of posts) {
		const postURL = `https://lipeng.ac/post/${post.id}`

		let postImgType = null
		if ((post.image || "").endsWith(".png")) {
			postImgType = "image/png"
		} else if ((post.image || "").endsWith(".jpg")) {
			postImgType = "image/jpeg"
		}

		feed.item({
			url: postURL,
			author: "Tony He",
			title: post.title,
			categories: Categories,
			date: new Date(post.date),
			description: sanitizeStr(post.excerpt.replace("&hellip;", "...")),
			custom_elements: [
				{
					"content:encoded": {
						_cdata: sanitizeStr(post.content),
					},
					"dc:creator": "Tony He",
				},
			],
			...(post.image &&
				postImgType && {
					enclosure: {
						url: post.image,
						type: postImgType,
						size: 512,
					},
				}),
		})
	}

	return feed.xml({ indent: true })
}

/* ── Sitemap ───────────────────────────────────────────────── */

const generateSitemap = (posts) => {
	const urls = posts
		.map(
			(post) => `
		<url>
				<loc>https://lipeng.ac/post/${post.id}</loc>
				<changefreq>monthly</changefreq>
				<priority>0.6</priority>
		</url>`
		)
		.join("")

	return `<?xml version="1.0" encoding="UTF-8"?>
	<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">${urls}
	</urlset>
`
}

/* ── llms.txt ──────────────────────────────────────────────── */

const generateLLMsTxt = (posts) => {
	const recentPosts = posts
		.slice(0, 5)
		.map(
			(post) =>
				`- [${post.title}](https://lipeng.ac/post/${post.id}): Published ${new Date(post.date).toLocaleDateString()}`
		)
		.join("\n")

	const categoryLinks = Categories.map(
		(category) =>
			`- [${category.charAt(0).toUpperCase() + category.slice(1)} Posts](https://lipeng.ac/cate/${category}): Articles categorized under ${category}`
	).join("\n")

	return `# Tony He

> Personal website, academic profile, and blog of Tony (Lipeng) He - A collection of thoughts, experiences, publications, and insights across technology, life, and personal development.

This is Tony He's personal website featuring his academic profile, blog posts, reading lists, and various research and software projects. The site covers topics ranging from technology and programming to personal reflections and life experiences. Content is primarily in Chinese with some English posts.

## Recent Posts

${recentPosts}

## Content Categories

${categoryLinks}

## Site Resources

- [RSS Feed](https://lipeng.ac/feed): Subscribe to get updates on new posts
- [Reading List](https://lipeng.ac/reading-list): Curated collection of recommended books and articles
- [Friends](https://lipeng.ac/friends): Links to friends' websites and blogs
- [About](https://lipeng.ac): Main page with site overview and latest content

## Optional

- [Sitemap](https://lipeng.ac/sitemap.xml): Complete list of all pages for search engines
- [Sponsor](https://lipeng.ac/sponsor): Support the author's work
- [Web3 Projects](https://lipeng.ac/web3): Blockchain and Web3 related content
- [Podcasts](https://lipeng.ac/podcasts): Podcast appearances and audio content
`
}

/* ── Main ──────────────────────────────────────────────────── */

const posts = getPosts()

const files = [
	["feed.xml", generateRSS(posts)],
	["sitemap.xml", generateSitemap(posts)],
	["llms.txt", generateLLMsTxt(posts)],
]

for (const [name, content] of files) {
	fs.writeFileSync(path.join(PUBLIC_DIR, name), content, "utf-8")
	console.log(`Generated public/${name}`)
}
