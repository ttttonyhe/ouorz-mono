import format from "comma-number"
import useSWR from "swr"
import fetcher from "~/lib/fetcher"
import MetricCard from "./Card"

export default function GithubFollowerMetric() {
	const { data } = useSWR("api/github", fetcher)

	const followers = format(data?.followers)
	const link = "https://github.com/ttttonyhe"

	return (
		<MetricCard
			icon="github"
			value={followers}
			description="Github Followers"
			link={link}
			colorHex="#9CA3AF"
		/>
	)
}
