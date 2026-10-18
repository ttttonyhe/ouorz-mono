import format from "comma-number"
import useSWR from "swr"
import { z } from "zod"

import createFetcher from "~/lib/fetcher"

import MetricCard from "./Card"

const fetchFollowers = createFetcher(
	z.object({ followers: z.number().int().nonnegative() })
)

export default function TwitterMetric() {
	const { data } = useSWR("/api/twitter", fetchFollowers)

	const followers = data && format(data.followers)
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
