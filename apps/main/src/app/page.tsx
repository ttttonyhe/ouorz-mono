import Page from '~/components/Page'
import List from '~/components/List'
import Top from '~/components/Top'
import getApi from '~/utilities/api'

/**
 * Fetch post data of pinned posts from WP REST API
 *
 * @return {*}  {Promise<WPPost[]>}
 */
const getPinnedPosts = async (): Promise<WPPost[]> => {
	const res = await fetch(
		getApi({
			sticky: true,
			perPage: 10,
			cateExclude: '5,2,74',
		}),
		{ next: { revalidate: 36000 } }
	)
	const data = await res.json()
	return data || []
}

/**
 * Generate a greeting string randomly chosen from a few options
 *
 * @return {*}  {string}
 */
const getGreeting = (): string => {
	return [" there, it's Tony", ', Tony here', ", I'm Tony"][
		Math.floor(Math.random() * 10) % 3
	]
}

const HomePage = async () => {
	const stickyPosts = await getPinnedPosts()
	const greeting = getGreeting()

	return (
		<Page>
			<div className="lg:mt-20 mt-0 lg:pt-0 pt-24">
				<div>
					<h1 className="flex items-center font-medium text-3xl leading-14 lg:text-1 text-black dark:text-white tracking-wide mb-0.5 whitespace-nowrap">
						<span className="animate-waveHand hover:animate-waveHandAgain inline-block cursor-pointer mr-2.5">
							👋
						</span>{' '}
						Hey{greeting}
						<a
							href="https://cal.com/tonyhe/15min"
							className="ml-2 mt-0.5 hidden lg:block"
							target="_blank"
							rel="noreferrer"
						>
							<span className="text-sm flex items-center ml-2 py-1 px-2.5 border border-gray-400 hover:shadow-sm hover:border-gray-500 hover:text-gray-600 text-gray-500 dark:text-white dark:border-white dark:hover:opacity-80 rounded-md tracking-normal">
								Let&apos;s chat →
							</span>
						</a>
					</h1>

					<div className="group">
						<p className="group-hover:hidden absolute text-3 lg:text-2 text-gray-500 dark:text-gray-200 leading-14 tracking-wider font-light pl-1.5 pb-1.5 pt-1">
							I&apos;m currently living an absolutely not meaningless life with
							totally not unachievable goals.
						</p>
						<p className="group-hover:animate-none animate-completePulse text-3 lg:text-2 text-gray-500 dark:text-gray-200 leading-14 tracking-wider font-light pl-1.5 pb-1.5 pt-1">
							I&apos;m currently living a<del>n absolutely not</del> meaningless
							life with <del>totally not</del> unachievable goals.
						</p>
					</div>
				</div>
				<Top />
			</div>
			<div className="mt-10">
				{stickyPosts.length > 0 && (
					<List.Static posts={stickyPosts} sticky={true} />
				)}
			</div>
			<div className="mt-5">
				<List.Infinite type="index" />
			</div>
		</Page>
	)
}

export default HomePage
