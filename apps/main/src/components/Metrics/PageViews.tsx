import useSWR from "swr"
import { z } from "zod"
import createFetcher from "~/lib/fetcher"
import { formatNumber } from "~/utilities/format"

import MetricCard from "./Card"

const fetchViews = createFetcher(
	z.object({ views: z.number().int().nonnegative() })
)

export default function PageViewsMetric() {
	const { data } = useSWR("/api/analytics", fetchViews)

	return (
		<MetricCard
			icon="growth"
			value={data && formatNumber(data.views)}
			description="7-Day Views"
			link="https://analytics.ouorz.com/share/E4O9QpCn/ouorz-next"
			colorHex="#9CA3AF"
		/>
	)
}
