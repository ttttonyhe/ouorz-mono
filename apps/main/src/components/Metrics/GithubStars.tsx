import useSWR from 'swr'
import format from 'comma-number'
import fetcher from '~/lib/fetcher'
import MetricCard from './Card'

export default function GithubStarMetric() {
	const { data } = useSWR('api/github', fetcher)

	const stars = format(data?.stars)
	const link = 'https://github.com/HelipengTony'

	return (
		<MetricCard
			icon="star"
			value={stars}
			footer="Github Stars"
			link={link}
			colorHex="#9CA3AF"
		/>
	)
}
