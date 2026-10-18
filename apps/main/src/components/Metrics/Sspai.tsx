import format from "comma-number"
import useSWR from "swr"
import { z } from "zod"

import createFetcher from "~/lib/fetcher"

import MetricCard from "./Card"

const fetchFollowers = createFetcher(
	z.object({ sspaiFollowers: z.number().int().nonnegative() })
)

export default function SspaiMetric() {
	const { data } = useSWR("/api/substats", fetchFollowers)

	const followers = data && format(data.sspaiFollowers)
	const link = "https://sspai.com/u/tonyhe"

	return (
		<MetricCard
			icon="flag"
			value={followers}
			description="SSPAI Followers"
			link={link}
			colorHex="#da282b"
		/>
	)
}
