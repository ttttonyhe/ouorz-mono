import format from "comma-number"
import useSWR from "swr"
import fetcher from "~/lib/fetcher"
import MetricCard from "./Card"

export default function ZhihuMetric() {
	const { data } = useSWR("api/substats", fetcher)

	const followers = format(data?.zhihuFollowers)
	const link = "https://www.zhihu.com/people/ttttonyhe"

	return (
		<MetricCard
			icon="thumbDown"
			value={followers}
			description="Zhihu Followers"
			link={link}
			colorHex="#9CA3AF"
		/>
	)
}
