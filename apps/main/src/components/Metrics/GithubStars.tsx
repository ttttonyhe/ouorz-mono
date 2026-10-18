import format from "comma-number"
import useSWR from "swr"

import { githubMetricsSchema } from "~/constants/propTypes"
import createFetcher from "~/lib/fetcher"

import MetricCard from "./Card"

const fetchGithub = createFetcher(githubMetricsSchema)

export default function GithubStarMetric() {
	const { data } = useSWR("/api/github", fetchGithub)

	const stars = data && format(data.stars)
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
