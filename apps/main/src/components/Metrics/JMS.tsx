import useSWR from 'swr'
import fetcher from '~/lib/fetcher'
import MetricCard from './Card'

export default function JMSMetric() {
	const { data } = useSWR('api/jms', fetcher)

	const used = parseInt(data?.used).toString()
	const total = parseInt(data?.total).toString()
	const link = 'https://justmysocks2.net/members/clientarea.php'

	return (
		<MetricCard
			icon="plane"
			value={used}
			subValue={total}
			footer="Just My Socks"
			link={link}
			colorHex="#9CA3AF"
		/>
	)
}
