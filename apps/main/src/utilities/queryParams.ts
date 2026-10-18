import type { NextApiRequest } from "next"

type QueryValue = NextApiRequest["query"][string]

/** First value of a query parameter, or undefined when it was not provided. */
export const readParam = (value: QueryValue): string | undefined =>
	Array.isArray(value) ? value[0] : value

/** Whether a query parameter was provided and equals the expected value. */
export const paramEquals = (value: QueryValue, expected: string): boolean =>
	readParam(value) === expected
