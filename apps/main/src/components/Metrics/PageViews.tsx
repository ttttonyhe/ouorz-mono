import format from "comma-number"
import useSWR from "swr"
import fetcher from "~/lib/fetcher"
import MetricCard from "./Card"

export default function PageViewsMetric() {
	const { data } = useSWR("api/analytics", fetcher)

	const views = format(data?.views)
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
