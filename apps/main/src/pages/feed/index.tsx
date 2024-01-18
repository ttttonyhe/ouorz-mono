/* eslint-disable camelcase */
import { GetServerSideProps } from "next"
import { FC } from "react"
import RSS from "rss"
import getApi from "~/utilities/api"
import { sanitizeStr } from "~/utilities/string"

type RSSDataResponse = {
	post_id: number
	post_date_gmt: string
	post_title: string
	post_excerpt: string
	post_content: string
}[]

const RSSFeed: FC = () => null

export const getServerSideProps: GetServerSideProps = async ({ res }) => {
	if (res) {
		const feed = new RSS({
			title: "Tony He",
			language: "zh-cn",
			webMaster: "tony.hlp@hotmail.com",
			managingEditor: "tony.hlp@hotmail.com",
			generator: "Next.js / WordPress",
			site_url: "https://www.ouorz.com",
			feed_url: "https://www.ouorz.com/feed",
			image_url: "https://www.ouorz.com/tony.png",
			docs: "https://www.rssboard.org/rss-specification",
			copyright: `© ${new Date().getFullYear()} Tony He`,
			categories: ["personal", "technology", "life", "blogs"],
			description:
				"Living an absolutely not meaningless life with totally not unachievable goals.",
		})

		const response = await fetch(
			getApi({
				rssData: true,
			})
		)

		const data: RSSDataResponse = await response.json()

		data.map(
			({ post_id, post_date_gmt, post_title, post_excerpt, post_content }) => {
				const postURL = `https://www.ouorz.com/post/${post_id}`

				feed.item({
					title: post_title,
					url: postURL,
					description: sanitizeStr(post_excerpt.replace("&hellip;", "...")),
					custom_elements: [
						{
							"content:encoded": {
								_cdata: post_content,
							},
						},
					],
					date: new Date(post_date_gmt),
					author: "Tony He",
				})
			}
		)

		res.setHeader("Content-Type", "application/rss+xml; charset=utf-8")
		res.write(feed.xml({ indent: true }))
		res.end()
	}

	return {
		props: {},
	}
}

export default RSSFeed
