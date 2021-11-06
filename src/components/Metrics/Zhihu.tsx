import useSWR from 'swr'
import format from 'comma-number'
import fetcher from '~/lib/fetcher'
import MetricCard from './Card'

export default function ZhihuMetric() {
	const { data } = useSWR('api/substats', fetcher)

	const followers = format(data?.zhihuFollowers)
	const link = 'https://www.zhihu.com/people/helipengtony'

	return (
		<MetricCard
			icon="thumbDown"
			value={followers}
			footer="Zhihu Followers"
			link={link}
			colorHex="#9CA3AF"
		/>
	)
}
