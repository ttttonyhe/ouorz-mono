import useSWR from "swr"
import { z } from "zod"

import api from "~/utilities/api"

const previewSchema = z.object({ mdxHtml: z.string().nullable() })

const fetchFresh = (url: string) =>
	fetch(url, { cache: "no-store", signal: AbortSignal.timeout(15000) }).then(
		async (response) => {
			if (!response.ok) throw new Error(`Request failed: ${response.status}`)
			return previewSchema.parse(await response.json())
		}
	)

/** Posts written before the MDX migration are stored as HTML and render as-is. */
const isHtmlContent = (raw: string) => /<\w+[\s\S]*>/u.test(raw)

interface MdxPreview {
	mdxHtml: string | null
	isLoading: boolean
}

const useMdxPreview = (
	id: string | number | undefined,
	rawContent: string | undefined,
	enabled: boolean
): MdxPreview => {
	const shouldSerialize =
		enabled && id !== undefined && !isHtmlContent(rawContent ?? "")

	const { data, isLoading } = useSWR(
		shouldSerialize
			? `${api.internal.post({ id: Number(id) })}&render=html`
			: null,
		fetchFresh
	)

	return {
		mdxHtml: shouldSerialize ? (data?.mdxHtml ?? null) : null,
		isLoading: shouldSerialize && isLoading,
	}
}

export default useMdxPreview
