import useSWR from "swr"
import { z } from "zod"
import createFetcher from "~/lib/fetcher"
import { formatNumber } from "~/utilities/format"

import MetricCard from "./Card"

const fetchFollowers = createFetcher(
	z.object({ sspaiFollowers: z.number().int().nonnegative() })
)

export default function SspaiMetric() {
	const { data } = useSWR("/api/substats", fetchFollowers)

	return (
		<MetricCard
			icon="flag"
			value={data && formatNumber(data.sspaiFollowers)}
			description="SSPAI Followers"
			link="https://sspai.com/u/tonyhe"
			colorHex="#da282b"
		/>
	)
}
