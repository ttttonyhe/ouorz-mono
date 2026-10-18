import format from "comma-number"
import useSWR from "swr"
import { z } from "zod"

import createFetcher from "~/lib/fetcher"

import MetricCard from "./Card"

const fetchViews = createFetcher(
	z.object({ views: z.number().int().nonnegative() })
)

export default function PageViewsMetric() {
	const { data } = useSWR("/api/analytics", fetchViews)

	const views = data && format(data.views)
	const link = "https://analytics.ouorz.com/share/E4O9QpCn/ouorz-next"

	return (
		<MetricCard
			icon="growth"
			value={views}
			description="7-Day Views"
			link={link}
			colorHex="#9CA3AF"
		/>
	)
}
