import format from "comma-number"
import useSWR from "swr"
import fetcher from "~/lib/fetcher"
import MetricCard from "./Card"

export default function PostViewsMetric() {
	const { data } = useSWR("api/posts", fetcher)

	const views = format(data?.views)
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
