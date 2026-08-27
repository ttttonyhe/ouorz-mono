import type { NextApiRequest, NextApiResponse } from "next"
import Parser from "rss-parser"

import { GOODREADS_API } from "~/constants/apiURLs"
import { readParam } from "~/utilities/queryParams"

export type Book = {
	title: string
	link: string
	imageURL: string
	author: string
	dateAdded: string
}

type ResDataType = {
	books: Book[]
}

const parser = new Parser()
const feedShelfNames = {
	currentlyReading: "currently-reading",
	toRead: "to-read",
	read: "read",
	all: "#ALL#",
	mustRead: "must-read",
} satisfies Record<string, string>

type FeedShelf = keyof typeof feedShelfNames

const isFeedShelf = (value: string): value is FeedShelf =>
	Object.hasOwn(feedShelfNames, value)

const goodreads = async (
	req: NextApiRequest,
	res: NextApiResponse<ResDataType>
) => {
	const requested = readParam(req.query.shelf) ?? ""
	const shelf = isFeedShelf(requested) ? requested : "all"
	const feed = await parser.parseURL(
		`${GOODREADS_API.RSS}&shelf=${feedShelfNames[shelf]}`
	)

	const books = feed.items.map((item) => {
		const imageURLArray = item.content.match(/<img.*src="(.*)"/u)[1].split(".")
		const imageExtension = imageURLArray.at(-1)
		const imageURL = `${imageURLArray
			.slice(0, imageURLArray.length - 2)
			.join(".")}._SX166_.${imageExtension}`

		return {
			title: item.title,
			imageURL: imageURL,
			link: item.link.split("?")[0],
			author: item.content.match(/author: (.*)<br\/>/u)[1],
			dateAdded: item.content.match(/date added: (.*)<br\/>/u)[1],
		}
	})

	res.setHeader(
		"Cache-Control",
		"public, s-maxage=1200, stale-while-revalidate=600"
	)

	return res.status(200).json({
		books,
	})
}

export default goodreads
