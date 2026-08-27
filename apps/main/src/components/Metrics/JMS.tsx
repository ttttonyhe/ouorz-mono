import useSWR from "swr"

import fetcher from "~/lib/fetcher"

import MetricCard from "./Card"

export default function JMSMetric() {
	const { data } = useSWR("api/jms", fetcher)

	const used = Math.trunc(Number(data?.used)).toString()
	const total = Math.trunc(Number(data?.total)).toString()
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
