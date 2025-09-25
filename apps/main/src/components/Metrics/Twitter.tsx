import format from "comma-number"
import useSWR from "swr"
import fetcher from "~/lib/fetcher"
import MetricCard from "./Card"

export default function TwitterMetric() {
	const { data } = useSWR("api/twitter", fetcher)

	const followers = format(data?.followers)
	const link = "https://twitter.com/ttttonyhe"

	return (
		<MetricCard
			icon="twitter"
			value={followers}
			description="Twitter Followers"
			link={link}
			colorHex="#3ba2f2"
		/>
	)
}
