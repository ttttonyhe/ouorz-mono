import Parser from "rss-parser"
import { z } from "zod"

import { GOODREADS_API } from "~/constants/apiURLs"

const shelfNames = {
	currentlyReading: "currently-reading",
	toRead: "to-read",
	read: "read",
	all: "#ALL#",
	mustRead: "must-read",
} as const

export type GoodreadsShelf = keyof typeof shelfNames

export const isGoodreadsShelf = (value: string): value is GoodreadsShelf =>
	Object.hasOwn(shelfNames, value)

const bookSchema = z.object({
	title: z.string().trim().min(1),
	link: z.url({ protocol: /^https$/u }).transform((link) => link.split("?")[0]),
	imageURL: z
		.url({ protocol: /^https$/u })
		.transform((url) => url.replace(/\._[^.]+_\.(\w+)$/u, "._SX166_.$1")),
	author: z.string().trim().min(1),
	dateAdded: z
		.string()
		.regex(/ [+-]\d{4}$/u)
		.refine((date) => !Number.isNaN(Date.parse(date)))
		.transform((date) => {
			const offset = date.slice(-5)
			const minutes = Number(offset.slice(1, 3)) * 60 + Number(offset.slice(3))
			const direction = offset.startsWith("-") ? -1 : 1
			return new Date(Date.parse(date) + direction * minutes * 60_000)
				.toISOString()
				.slice(0, 10)
				.replaceAll("-", "/")
		}),
})

export type Book = z.infer<typeof bookSchema>

interface GoodreadsFields {
	imageURL: string
	author: string
	dateAdded: string
}

const parser = new Parser<Record<string, never>, GoodreadsFields>({
	customFields: {
		item: [
			["book_image_url", "imageURL"],
			["author_name", "author"],
			["user_date_added", "dateAdded"],
		],
	},
})

export const getGoodreadsBooks = async (
	shelf: GoodreadsShelf
): Promise<Book[]> => {
	const url = new URL(GOODREADS_API.RSS)
	url.searchParams.set("shelf", shelfNames[shelf])

	// rss-parser's own timeout rejects without aborting the network request.
	const response = await fetch(url, {
		cache: "no-store",
		signal: AbortSignal.timeout(10_000),
	})
	if (!response.ok)
		throw new Error(`Goodreads ${shelf} feed returned ${response.status}`)

	const feed = await parser.parseString(await response.text())
	return z.array(bookSchema).parse(feed.items)
}
