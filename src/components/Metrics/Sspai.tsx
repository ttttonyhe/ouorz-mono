import useSWR from 'swr'
import format from 'comma-number'
import fetcher from '~/lib/fetcher'
import MetricCard from './Card'

export default function SspaiMetric() {
	const { data } = useSWR('api/substats', fetcher)

	const followers = format(data?.sspaiFollowers)
	const link = 'https://sspai.com/u/tonyhe'

	return (
		<MetricCard
			icon="flag"
			value={followers}
			footer="SSPAI Followers"
			link={link}
			colorHex="#da282b"
		/>
	)
}
