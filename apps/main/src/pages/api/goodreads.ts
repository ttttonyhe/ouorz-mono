import type { NextApiRequest, NextApiResponse } from "next"
import Parser from "rss-parser"
import { GOODREADS_API } from "~/constants/apiURLs"

export type Book = {
	title: string
	link: string
	imageURL: string
	author: string
	dateAdded: string
}

type ReqQueryType = {
	shelf: string
}

type ResDataType = {
	books: Book[]
}

const parser = new Parser()
const feedShelfNames = {
	currentlyReading: "currently-reading",
	read: "read",
	all: "#ALL#",
}

const goodreads = async (
	req: NextApiRequest,
	res: NextApiResponse<ResDataType>
) => {
	const { shelf } = req.query as ReqQueryType
	const feed = await parser.parseURL(
		`${GOODREADS_API.RSS}&shelf=${
			feedShelfNames[shelf] || feedShelfNames["currentlyReading"]
		}`
	)

	const books = feed.items.map((item) => {
		const imageURLArray = item.content.match(/<img.*src="(.*)"/)[1].split(".")
		const imageExtension = imageURLArray[imageURLArray.length - 1]
		const imageURL = `${imageURLArray
			.slice(0, imageURLArray.length - 2)
			.join(".")}._SX166_.${imageExtension}`

		return {
			title: item.title,
			link: item.link.split("?")[0],
			imageURL: imageURL,
			author: item.content.match(/author: (.*)<br\/>/)[1],
			dateAdded: item.content.match(/date added: (.*)<br\/>/)[1],
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
