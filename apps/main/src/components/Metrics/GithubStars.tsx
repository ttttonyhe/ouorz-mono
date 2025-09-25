import format from "comma-number"
import useSWR from "swr"
import fetcher from "~/lib/fetcher"
import MetricCard from "./Card"

export default function GithubStarMetric() {
	const { data } = useSWR("api/github", fetcher)

	const stars = format(data?.stars)
	const link = "https://github.com/ttttonyhe"

	return (
		<MetricCard
			icon="star"
			value={stars}
			description="Github Stars"
			link={link}
			colorHex="#9CA3AF"
		/>
	)
}
