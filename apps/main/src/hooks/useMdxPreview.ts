import type { MDXRemoteSerializeResult } from "next-mdx-remote"
import useSWR from "swr"

import getAPI from "~/utilities/api"

const fetchFresh = (url: string) =>
	fetch(url, { cache: "no-store" }).then((response) => {
		if (!response.ok) throw new Error(`Request failed: ${response.status}`)
		return response.json()
	})

/** Posts written before the MDX migration are stored as HTML and render as-is. */
const isHtmlContent = (raw: string) => /<\w+[\s\S]*>/u.test(raw)

interface MdxPreview {
	mdxSource: MDXRemoteSerializeResult | null
	isLoading: boolean
}

/**
 * Serializes a post's raw content to MDX on the server so the reader can render
 * it. HTML posts skip the request entirely.
 */
const useMdxPreview = (
	id: string | number | undefined,
	rawContent: string | undefined,
	enabled: boolean
): MdxPreview => {
	const shouldSerialize =
		enabled && id !== undefined && !isHtmlContent(rawContent ?? "")

	const { data, isLoading } = useSWR(
		shouldSerialize
			? `${getAPI("internal", "post", { id: Number(id) })}&render=mdx`
			: null,
		fetchFresh
	)

	return {
		mdxSource: shouldSerialize ? (data?.mdxSource ?? null) : null,
		isLoading: shouldSerialize && isLoading,
	}
}

export default useMdxPreview
