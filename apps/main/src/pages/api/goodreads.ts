import type { NextApiRequest, NextApiResponse } from 'next'
import Parser from 'rss-parser'

type Book = {
	title: string
	link: string
	imageURL: string
	author: string
	dateAdded: string | Date
}

type ReqQueryType = {
	shelf: string
}

type ResDataType = {
	books: Book[]
}

const parser = new Parser()
const feedURL =
	'https://www.goodreads.com/review/list_rss/146097881?key=YdulPNTVXpB1iC4Hx05BcR-W8j9wAT4Nip56cjwulPilbx02'
const feedShelfNames = {
	currentlyReading: 'currently-reading',
	all: '#ALL#',
}

const goodreads = async (
	req: NextApiRequest,
	res: NextApiResponse<ResDataType>
) => {
	const { shelf } = req.query as ReqQueryType
	const feed = await parser.parseURL(
		`${feedURL}&shelf=${
			feedShelfNames[shelf] || feedShelfNames['currentlyReading']
		}`
	)

	const books = feed.items.map((item) => {
		const imageURLArray = item.content.match(/<img.*src="(.*)"/)[1].split('.')
		const imageExtension = imageURLArray[imageURLArray.length - 1]
		const imageURL = `${imageURLArray
			.slice(0, imageURLArray.length - 2)
			.join('.')}._SX166_.${imageExtension}`

		return {
			title: item.title,
			link: item.link.split('?')[0],
			imageURL: imageURL,
			author: item.content.match(/author: (.*)<br\/>/)[1],
			dateAdded: item.content.match(/date added: (.*)<br\/>/)[1],
		}
	})

	res.setHeader(
		'Cache-Control',
		'public, s-maxage=1200, stale-while-revalidate=600'
	)

	return res.status(200).json({
		books,
	})
}

export default goodreads
