import format from "comma-number"
import useSWR from "swr"

import { postMetricsSchema } from "~/constants/propTypes"
import createFetcher from "~/lib/fetcher"

import MetricCard from "./Card"

const fetchPosts = createFetcher(postMetricsSchema)

export default function PostsMetric() {
	const { data } = useSWR("/api/posts", fetchPosts)

	const count = data && format(data.count)
	const link = "https://blog.ouorz.com/wp-admin"

	return (
		<MetricCard
			icon="count"
			value={count}
			description="Total Posts"
			link={link}
			colorHex="#F59E0B"
		/>
	)
}
