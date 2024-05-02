import MetricCard from "./Card"
import useSWR from "swr"
import fetcher from "~/lib/fetcher"

export default function JMSMetric() {
	const { data } = useSWR("api/jms", fetcher)

	const used = parseInt(data?.used).toString()
	const total = parseInt(data?.total).toString()
	const link = "https://justmysocks.net/members/clientarea.php"

	return (
		<MetricCard
			icon="plane"
			value={used}
			denominator={total}
			description="JustMySocks Network"
			link={link}
			colorHex="#3ba2f2"
		/>
	)
}
