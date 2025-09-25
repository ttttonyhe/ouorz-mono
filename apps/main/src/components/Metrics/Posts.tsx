import format from "comma-number"
import useSWR from "swr"
import fetcher from "~/lib/fetcher"
import MetricCard from "./Card"

export default function PostsMetric() {
	const { data } = useSWR("api/posts", fetcher)

	const count = format(data?.count)
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
