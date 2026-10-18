import type { NextApiRequest, NextApiResponse } from "next"

import {
	getGoodreadsBooks,
	isGoodreadsShelf,
	type Book,
} from "~/content/goodreads"
import { apiHandler } from "~/lib/apiHandler"
import { readParam } from "~/utilities/queryParams"

type GoodreadsResponse = { books: Book[] } | { error: string }

const goodreads = async (
	req: NextApiRequest,
	res: NextApiResponse<GoodreadsResponse>
) => {
	if (Array.isArray(req.query.shelf)) {
		return res.status(400).json({ error: "Invalid Goodreads shelf" })
	}
	const shelf = readParam(req.query.shelf) ?? "all"
	if (!isGoodreadsShelf(shelf)) {
		return res.status(400).json({ error: "Unknown Goodreads shelf" })
	}

	const books = await getGoodreadsBooks(shelf)
	res.setHeader(
		"Cache-Control",
		"public, s-maxage=1200, stale-while-revalidate=600"
	)
	return res.status(200).json({ books })
}

export default apiHandler(["GET", "HEAD"], goodreads)
