import type { GetServerSideProps } from "next"
import type { FC } from "react"
import getAPI from "~/utilities/api"

type RSSDataResponse = {
	post_id: number
	post_date_gmt: string
	post_title: string
	post_excerpt: string
	post_content: string
	post_img: string | null
}[]

const LLMsTxt: FC = () => null

const Categories = ["personal", "technology", "life", "blogs"]

export const getServerSideProps: GetServerSideProps = async ({ res }) => {
	if (res) {
		// Get recent posts data for dynamic content
		const response = await fetch(getAPI("internal", "rssData"), {
			next: {
				revalidate: 24 * 3600,
			},
		})
		const data: RSSDataResponse = await response.json()

		// Generate recent posts list (limit to top 5)
		const recentPosts = data
			.slice(0, 5)
			.map(
				(post) =>
					`- [${post.post_title}](https://lipeng.ac/post/${post.post_id}): Published ${new Date(post.post_date_gmt).toLocaleDateString()}`
			)
			.join("\n")

		// Generate category links
		const categoryLinks = Categories.map(
			(category) =>
				`- [${category.charAt(0).toUpperCase() + category.slice(1)} Posts](https://lipeng.ac/cate/${category}): Articles categorized under ${category}`
		).join("\n")

		const llmsTxt = `# Tony He

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

		// Set appropriate headers
		res.setHeader("Vercel-CDN-Cache-Control", `max-age=${3600 * 24 * 7}`)
		res.setHeader("CDN-Cache-Control", `max-age=${3600 * 24}`)
		res.setHeader("Cache-Control", "max-age=3600")
		res.setHeader("Content-Type", "text/plain; charset=utf-8")
		res.write(llmsTxt)
		res.end()
	}

	return {
		props: {},
	}
}

export default LLMsTxt
