import useSWR from 'swr'
import format from 'comma-number'
import fetcher from '~/lib/fetcher'
import MetricCard from './Card'

export default function PostsMetric() {
	const { data } = useSWR('api/posts', fetcher)

	const views = format(data?.views)
	const link = 'https://blog.ouorz.com/wp-admin'

	return (
		<MetricCard
			icon="eye"
			value={views}
			footer="Blog Post Views"
			link={link}
			colorHex="#F59E0B"
		/>
	)
}
