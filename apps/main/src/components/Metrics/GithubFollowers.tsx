import format from "comma-number"
import useSWR from "swr"

import { githubMetricsSchema } from "~/constants/propTypes"
import createFetcher from "~/lib/fetcher"

import MetricCard from "./Card"

const fetchGithub = createFetcher(githubMetricsSchema)

export default function GithubFollowerMetric() {
	const { data } = useSWR("/api/github", fetchGithub)

	const followers = data && format(data.followers)
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
