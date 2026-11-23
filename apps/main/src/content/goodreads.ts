import * as Sentry from "@sentry/nextjs"
import { z } from "zod"
import { GOODREADS_API } from "~/constants/apiURLs"
import { ApiError } from "~/lib/apiHandler"

const SHELF_CONFIG = {
	mustRead: { shelf: "must-read", widgetId: 101 },
	currentlyReading: { shelf: "currently-reading", widgetId: 102 },
	read: { shelf: "read", widgetId: 103 },
	toRead: { shelf: "to-read", widgetId: 104 },
} as const

type ConcreteShelf = keyof typeof SHELF_CONFIG
export type GoodreadsShelf = ConcreteShelf | "all"

const CONCRETE_SHELVES: readonly ConcreteShelf[] = [
	"mustRead",
	"currentlyReading",
	"read",
	"toRead",
]

// Must match `images.remotePatterns` in next.config.ts so next/image never throws during SSR.
const ALLOWED_IMAGE_HOSTS = new Set(["i.gr-assets.com", "s.gr-assets.com"])

export const isGoodreadsShelf = (value: string): value is GoodreadsShelf =>
	value === "all" || Object.hasOwn(SHELF_CONFIG, value)

class GoodreadsParseError extends Error {
	constructor(message: string) {
		super(message)
		this.name = "GoodreadsParseError"
	}
}

const bookSchema = z.object({
	title: z.string().trim().min(1),
	link: z
		.url({ protocol: /^https$/u })
		.transform((link) => link.split("?")[0] ?? link),
	imageURL: z
		.url({ protocol: /^https$/u })
		.refine((url) => ALLOWED_IMAGE_HOSTS.has(new URL(url).hostname), {
			message: "Image host is not allowlisted for next/image",
		})
		.transform((url) => url.replace(/\._[^.]+_\.(\w+)$/u, "._SX166_.$1")),
	author: z.string().trim().min(1),
	dateAdded: z.string().trim().min(1).optional(),
})

export type Book = z.infer<typeof bookSchema>

const NAMED_ENTITIES = new Map<string, string>([
	["&amp;", "&"],
	["&lt;", "<"],
	["&gt;", ">"],
	["&quot;", '"'],
	["&apos;", "'"],
	["&nbsp;", " "],
	["&lsquo;", "\u2018"],
	["&rsquo;", "\u2019"],
	["&ldquo;", "\u201C"],
	["&rdquo;", "\u201D"],
	["&ndash;", "\u2013"],
	["&mdash;", "\u2014"],
	["&hellip;", "\u2026"],
])

const isValidUnicodeScalar = (code: number) =>
	Number.isInteger(code) &&
	code > 0 &&
	code <= 0x10ffff &&
	(code < 0xd800 || code > 0xdfff)

const decodeHtmlEntities = (value: string): string =>
	value.replaceAll(
		/&(?:[a-zA-Z]+|#\d+|#[xX][\da-fA-F]+);/gu,
		(entity): string => {
			const mapped = NAMED_ENTITIES.get(entity.toLowerCase())
			if (mapped) return mapped
			if (entity.startsWith("&#x") || entity.startsWith("&#X")) {
				const code = Number(`0x${entity.slice(3, -1)}`)
				return isValidUnicodeScalar(code) ? String.fromCodePoint(code) : entity
			}
			if (entity.startsWith("&#")) {
				const code = Number(entity.slice(2, -1))
				return isValidUnicodeScalar(code) ? String.fromCodePoint(code) : entity
			}
			return entity
		}
	)

const normalizeText = (value: string): string =>
	decodeHtmlEntities(value.replaceAll(/<[^>]*>/gu, " "))
		.replaceAll(/\s+/gu, " ")
		.trim()

interface DecodedEscape {
	char: string
	consumed: number
}

const decodeUnicodeEscape = (script: string, index: number): DecodedEscape => {
	if (script[index] === "{") {
		const closeBrace = script.indexOf("}", index + 1)
		const hex = closeBrace === -1 ? "" : script.slice(index + 1, closeBrace)
		const code = /^[\da-fA-F]+$/u.test(hex) ? Number(`0x${hex}`) : -1
		if (isValidUnicodeScalar(code)) {
			return {
				char: String.fromCodePoint(code),
				consumed: closeBrace - index + 1,
			}
		}
		return { char: "u", consumed: 0 }
	}

	const hex = script.slice(index, index + 4)
	if (!/^[\da-fA-F]{4}$/u.test(hex)) {
		return { char: "u", consumed: 0 }
	}

	const code = Number(`0x${hex}`)
	if (
		code >= 0xd800 &&
		code <= 0xdbff &&
		script.slice(index + 4, index + 6) === "\\u"
	) {
		const lowHex = script.slice(index + 6, index + 10)
		const low = /^[\da-fA-F]{4}$/u.test(lowHex) ? Number(`0x${lowHex}`) : -1
		if (low >= 0xdc00 && low <= 0xdfff) {
			return {
				char: String.fromCodePoint(
					0x10000 + ((code - 0xd800) << 10) + (low - 0xdc00)
				),
				consumed: 10,
			}
		}
	}

	return {
		char: isValidUnicodeScalar(code) ? String.fromCodePoint(code) : "\uFFFD",
		consumed: 4,
	}
}

const SIMPLE_JS_ESCAPES = new Map<string, string>([
	["n", "\n"],
	["r", "\r"],
	["t", "\t"],
	["b", "\b"],
	["f", "\f"],
	["v", "\v"],
	["0", "\0"],
])

const extractWidgetHtml = (script: string): string => {
	const prefix = "var widget_code = '"
	const start = script.indexOf(prefix)
	if (start === -1) {
		throw new GoodreadsParseError("Goodreads widget payload missing code")
	}

	let result = ""
	for (let i = start + prefix.length; i < script.length; i += 1) {
		const ch = script[i]
		if (ch === "'") return result
		if (ch !== "\\" || i + 1 >= script.length) {
			result += ch ?? ""
			continue
		}

		const next = script[i + 1]
		if (next === undefined) break
		i += 1

		const simple = SIMPLE_JS_ESCAPES.get(next)
		if (simple !== undefined) {
			result += simple
		} else if (next === "x") {
			const hex = script.slice(i + 1, i + 3)
			if (/^[\da-fA-F]{2}$/u.test(hex)) {
				result += String.fromCodePoint(Number(`0x${hex}`))
				i += 2
			} else {
				result += "x"
			}
		} else if (next === "u") {
			const decoded = decodeUnicodeEscape(script, i + 1)
			result += decoded.char
			i += decoded.consumed
		} else {
			result += next
		}
	}

	throw new GoodreadsParseError("Goodreads widget payload unterminated")
}

const deduplicateBooks = (books: readonly Book[]): Book[] => {
	const seen = new Set<string>()
	const unique: Book[] = []
	for (const book of books) {
		if (seen.has(book.link)) continue
		seen.add(book.link)
		unique.push(book)
	}
	return unique
}

const parseWidgetBooks = (script: string, widgetId: number): Book[] => {
	const html = extractWidgetHtml(script)
	if (!html.includes(`gr_custom_container_${widgetId}`)) {
		throw new GoodreadsParseError(
			`Goodreads widget container ${widgetId} missing`
		)
	}

	const itemMarker = new RegExp(
		`<div[^>]*class=["'][^"']*\\bgr_custom_each_container_${widgetId}\\b[^"']*["'][^>]*>`,
		"u"
	)
	const blocks = html.split(itemMarker).slice(1)
	if (blocks.length === 0 && html.includes("/review/show/")) {
		throw new GoodreadsParseError(
			`Goodreads widget item wrapper ${widgetId} changed`
		)
	}

	const titlePattern = new RegExp(
		`gr_custom_title_${widgetId}\\b[^>]*>[\\s\\S]*?<a\\b[^>]*href=["']([^"']+)["'][^>]*>([\\s\\S]*?)<\\/a>`,
		"u"
	)
	const authorPattern = new RegExp(
		`gr_custom_author_${widgetId}\\b[^>]*>[\\s\\S]*?<a\\b[^>]*>([\\s\\S]*?)<\\/a>`,
		"u"
	)

	const rawBooks = blocks.map((block) => {
		const titleMatch = titlePattern.exec(block)
		const authorMatch = authorPattern.exec(block)
		const imageURL =
			/<img\b(?:[^>"']|"[^"]*"|'[^']*')*\bsrc=["']([^"']+)["']/u.exec(
				block
			)?.[1] ?? ""
		const reviewLink = titleMatch?.[1]?.split("?")[0] ?? ""
		// Prefer public book pages because Goodreads review URLs redirect to sign-in.
		const bookId = /\/(\d+)(?:\._[^./]+_)?\.[a-z]+$/iu.exec(imageURL)?.[1]
		const link = bookId
			? `https://www.goodreads.com/book/show/${bookId}`
			: reviewLink

		return {
			title: normalizeText(titleMatch?.[2] ?? ""),
			author: normalizeText(authorMatch?.[1] ?? ""),
			link,
			imageURL,
		}
	})

	return deduplicateBooks(z.array(bookSchema).parse(rawBooks))
}

const fetchShelfBooks = async (shelfKey: ConcreteShelf): Promise<Book[]> => {
	const { shelf, widgetId } = SHELF_CONFIG[shelfKey]
	const url = new URL(GOODREADS_API.WIDGET)
	url.searchParams.set("cover_position", "left")
	url.searchParams.set("cover_size", "medium")
	url.searchParams.set("num_books", "100")
	url.searchParams.set("order", "d")
	url.searchParams.set("shelf", shelf)
	url.searchParams.set("show_author", "1")
	url.searchParams.set("show_cover", "1")
	url.searchParams.set("show_rating", "0")
	url.searchParams.set("show_review", "0")
	url.searchParams.set("show_tags", "0")
	url.searchParams.set("show_title", "1")
	url.searchParams.set("sort", "date_added")
	url.searchParams.set("widget_id", String(widgetId))

	try {
		const response = await fetch(url, {
			cache: "no-store",
			redirect: "error",
			headers: { "User-Agent": "lipeng-ac" },
			signal: AbortSignal.timeout(10_000),
		})
		if (!response.ok) {
			await response.body?.cancel()
			throw new GoodreadsParseError(
				`Goodreads ${shelfKey} widget returned ${response.status}`
			)
		}

		return parseWidgetBooks(await response.text(), widgetId)
	} catch (error) {
		if (error instanceof z.ZodError || error instanceof GoodreadsParseError) {
			Sentry.captureException(error)
		}
		throw new ApiError(502, "Goodreads service unavailable")
	}
}

export interface GoodreadsBooksResult {
	books: Book[]
	complete: boolean
}

export const getGoodreadsBooksWithStatus = async (
	shelf: GoodreadsShelf
): Promise<GoodreadsBooksResult> => {
	if (shelf !== "all") {
		return { books: await fetchShelfBooks(shelf), complete: true }
	}

	const results = await Promise.allSettled(
		CONCRETE_SHELVES.map((key) => fetchShelfBooks(key))
	)
	const fulfilled = results.filter(
		(result): result is PromiseFulfilledResult<Book[]> =>
			result.status === "fulfilled"
	)
	if (fulfilled.length === 0) {
		throw new ApiError(502, "Goodreads service unavailable")
	}

	return {
		books: deduplicateBooks(fulfilled.flatMap((result) => result.value)),
		complete: fulfilled.length === results.length,
	}
}

export const getGoodreadsBooks = async (
	shelf: GoodreadsShelf
): Promise<Book[]> => (await getGoodreadsBooksWithStatus(shelf)).books
