import format from "comma-number"
import useSWR from "swr"

import { postMetricsSchema } from "~/constants/propTypes"
import createFetcher from "~/lib/fetcher"

import MetricCard from "./Card"

const fetchPosts = createFetcher(postMetricsSchema)

export default function PostViewsMetric() {
	const { data } = useSWR("/api/posts", fetchPosts)

	const views = data && format(data.views)
	const link = "https://blog.ouorz.com/wp-admin"

	return (
		<MetricCard
			icon="eye"
			value={views}
			description="Total Views"
			link={link}
			colorHex="#F59E0B"
		/>
	)
}
