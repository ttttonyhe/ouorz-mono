import useSWR from 'swr'
import format from 'comma-number'
import fetcher from '~/lib/fetcher'
import MetricCard from './Card'

export default function GithubFollowerMetric() {
	const { data } = useSWR('api/github', fetcher)

	const followers = format(data?.followers)
	const link = 'https://github.com/HelipengTony'

	return (
		<MetricCard
			icon="users"
			value={followers}
			footer="Github Followers"
			link={link}
			colorHex="#9CA3AF"
		/>
	)
}
