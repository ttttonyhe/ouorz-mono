import useSWR from "swr"
import { githubMetricsSchema } from "~/constants/propTypes"
import createFetcher from "~/lib/fetcher"
import { formatNumber } from "~/utilities/format"

import MetricCard from "./Card"

const fetchGithub = createFetcher(githubMetricsSchema)

export default function GithubFollowerMetric() {
	const { data } = useSWR("/api/github", fetchGithub)

	return (
		<MetricCard
			icon="github"
			value={data && formatNumber(data.followers)}
			description="Github Followers"
			link="https://github.com/ttttonyhe"
			colorHex="#9CA3AF"
		/>
	)
}
