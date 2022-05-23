/* eslint-disable camelcase */
import { GetServerSideProps } from 'next'
import { FC } from 'react'
import RSS from 'rss'
import getApi from '~/utilities/api'

type RSSData = {
	ids: number[]
	titles: string[]
	contents: string[]
	dates: Date[]
}

const RSSFeed: FC = () => null

export const getServerSideProps: GetServerSideProps = async ({ res }) => {
	if (res) {
		const feed = new RSS({
			title: 'Tony He',
			site_url: 'https://www.ouorz.com',
			feed_url: 'https://www.ouorz.com/rss',
		})

		const response = await fetch(
			getApi({
				rssData: true,
			})
		)

		const data: RSSData = await response.json()

		data.titles.map((title, index) => {
			feed.item({
				title,
				url: `https://www.ouorz.com/post${data.ids[index]}`,
				description: data.contents[index],
				date: new Date(data.dates[index]),
			})
		})

		res.setHeader('Content-Type', 'text/xml')
		res.write(feed.xml())
		res.end()
	}

	return {
		props: {},
	}
}

export default RSSFeed
