/* eslint-disable camelcase */
import { GetServerSideProps } from "next"
import { FC } from "react"
import RSS from "rss"
import getAPI from "~/utilities/api"
import { sanitizeStr } from "~/utilities/string"

type RSSDataResponse = {
	post_id: number
	post_date_gmt: string
	post_title: string
	post_excerpt: string
	post_content: string
	post_img: string | null
}[]

const RSSFeed: FC = () => null

const Categories = ["personal", "technology", "life", "blogs"]

export const getServerSideProps: GetServerSideProps = async ({ res }) => {
	if (res) {
		const feed = new RSS({
			title: "Tony He",
			language: "zh-cn",
			categories: Categories,
			generator: "Next.js / WordPress",
			site_url: "https://www.ouorz.com",
			feed_url: "https://www.ouorz.com/feed",
			image_url: "https://www.ouorz.com/tony.png",
			webMaster: "tony.hlp@hotmail.com (Tony He)",
			managingEditor: "tony.hlp@hotmail.com (Tony He)",
			docs: "https://www.rssboard.org/rss-specification",
			copyright: `© ${new Date().getFullYear()} Tony He`,
			description:
				"Living an absolutely not meaningless life with totally not unachievable goals.",
		})

		const response = await fetch(getAPI("internal", "rssData"), {
			next: {
				revalidate: 24 * 3600,
			},
		})

		const data: RSSDataResponse = await response.json()

		data.map(
			({
				post_id,
				post_date_gmt,
				post_title,
				post_excerpt,
				post_content,
				post_img,
			}) => {
				const postURL = `https://www.ouorz.com/post/${post_id}`

				let postImgType = null
				if ((post_img || "").endsWith(".png")) {
					postImgType = "image/png"
				} else if ((post_img || "").endsWith(".jpg")) {
					postImgType = "image/jpeg"
				}

				feed.item({
					url: postURL,
					author: "Tony He",
					title: post_title,
					categories: Categories,
					date: new Date(post_date_gmt),
					description: sanitizeStr(post_excerpt.replace("&hellip;", "...")),
					custom_elements: [
						{
							"content:encoded": {
								_cdata: sanitizeStr(post_content),
							},
							"dc:creator": "Tony He",
						},
					],
					...(post_img &&
						postImgType && {
							enclosure: {
								url: post_img,
								type: postImgType,
								size: 512,
							},
						}),
				})
			}
		)

		res.setHeader("Vercel-CDN-Cache-Control", `max-age=${3600 * 24 * 7}`)
		res.setHeader("CDN-Cache-Control", `max-age=${3600 * 24}`)
		res.setHeader("Cache-Control", "max-age=3600")
		res.setHeader("Content-Type", "application/rss+xml; charset=utf-8")
		res.write(feed.xml({ indent: true }))
		res.end()
	}

	return {
		props: {},
	}
}

export default RSSFeed
