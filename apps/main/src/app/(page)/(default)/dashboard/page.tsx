import GithubStarMetric from '~/components/Metrics/GithubStars'
import GithubFollowerMetric from '~/components/Metrics/GithubFollowers'
import PostsMetric from '~/components/Metrics/Posts'
import NexmentMetric from '~/components/Metrics/Nexment'
import SspaiMetric from '~/components/Metrics/Sspai'
import TwitterMetric from '~/components/Metrics/Twitter'

const DashboardPage = () => {
	return (
		<div
			className="glowing-area mt-5 mb-10 grid lg:grid-cols-2 gap-4"
			data-cy="metricCards"
		>
			<NexmentMetric />
			<TwitterMetric />
			<SspaiMetric />
			<PostsMetric />
			<GithubStarMetric />
			<GithubFollowerMetric />
		</div>
	)
}

export default DashboardPage
