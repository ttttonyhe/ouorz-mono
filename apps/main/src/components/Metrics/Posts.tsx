import useSWR from "swr"
import { postMetricsSchema } from "~/constants/propTypes"
import createFetcher from "~/lib/fetcher"
import { formatNumber } from "~/utilities/format"

import MetricCard from "./Card"

const fetchPosts = createFetcher(postMetricsSchema)

export default function PostsMetric() {
	const { data } = useSWR("/api/posts", fetchPosts)

	return (
		<MetricCard
			icon="count"
			value={data && formatNumber(data.count)}
			description="Total Posts"
			link="https://github.com/ttttonyhe/ouorz-mono/tree/main/apps/main/content/posts"
			colorHex="#F59E0B"
		/>
	)
}
