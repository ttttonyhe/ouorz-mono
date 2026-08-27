import type { NextApiRequest } from "next"

type QueryValue = NextApiRequest["query"][string]

/** First value of a query parameter, or undefined when it was not provided. */
export const readParam = (value: QueryValue): string | undefined =>
	Array.isArray(value) ? value[0] : value

/** Query parameter parsed as a number, or undefined when absent or not numeric. */
export const readNumberParam = (value: QueryValue): number | undefined => {
	const raw = readParam(value)
	if (raw === undefined) return undefined
	const parsed = Number(raw)
	return Number.isNaN(parsed) ? undefined : parsed
}

/** Whether a query parameter was provided and equals the expected value. */
export const paramEquals = (value: QueryValue, expected: string): boolean =>
	readParam(value) === expected
