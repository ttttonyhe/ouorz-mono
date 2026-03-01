import MetricCard from "./Card"
import format from "comma-number"
import useSWR from "swr"
import fetcher from "~/lib/fetcher"

export default function SspaiMetric() {
	const { data } = useSWR("api/substats", fetcher)

	const followers = format(data?.sspaiFollowers)
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
