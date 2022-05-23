import useSWR from 'swr'
import format from 'comma-number'
import fetcher from '~/lib/fetcher'
import MetricCard from './Card'

export default function NexmentMetric() {
	const { data } = useSWR('api/nexment', fetcher)

	const count = format(data?.count)
	const link =
		'https://console.leancloud.cn/apps/NM8cdTVi8wqCmbeLPmiKCu79-gzGzoHsz/storage/data/nexment_comments'

	return (
		<MetricCard
			icon="chat"
			value={count}
			footer="Blog Comments"
			link={link}
			colorHex="#10B981"
		/>
	)
}
