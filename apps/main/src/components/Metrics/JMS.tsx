import useSWR from "swr"
import fetcher from "~/lib/fetcher"
import MetricCard from "./Card"

export default function JMSMetric() {
	const { data } = useSWR("api/jms", fetcher)

	const used = parseInt(data?.used).toString()
	const total = parseInt(data?.total).toString()
	const link = "https://justmysocks5.net/members/clientarea.php"

	return (
		<MetricCard
			icon="plane"
			value={used}
			denominator={total}
			description="Just My Socks"
			link={link}
			colorHex="#3ba2f2"
		/>
	)
}
