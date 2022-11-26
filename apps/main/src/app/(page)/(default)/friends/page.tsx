import Image from 'next/image'
import trimStr from '~/utilities/trimString'
import getApi from '~/utilities/api'
import { GlowingBackground } from '~/components/Visual'

type Stats = {
	status: boolean
	count: number
	views: number
}

/**
 * Get post statistics (eg. number of posts) from WP REST API
 *
 * @return {*}  {Promise<Number>}
 */
const getPostStats = async (): Promise<Stats> => {
	const res = await fetch(
		getApi({
			count: true,
		}),
		{
			next: {
				revalidate: 24 * 60 * 60,
			},
		}
	)
	const data = await res.json()
	return data
}

/**
 * Get posts in the friend category from WP REST API
 *
 * @param {number} count
 * @return {*} Promise<WPPost[]>
 */
const getFriends = async (count: number): Promise<WPPost[]> => {
	const res = await fetch(
		getApi({
			cate: '2',
			perPage: count,
		}),
		{
			next: {
				revalidate: 24 * 60 * 60,
			},
		}
	)
	const data = await res.json()
	return data
}

const FriendsPage = async () => {
	const friendsStats = await getPostStats()
	const friends = await getFriends(friendsStats.count)

	return (
		<div
			className="mt-5 grid grid-cols-2 gap-4 glowing-area"
			data-cy="friendsItems"
		>
			{friends.map((item, index) => {
				return (
					<div
						className="glowing-div cursor-pointer hover:shadow-md transition-shadow shadow-sm border bg-white dark:bg-gray-800 dark:border-gray-800 items-center rounded-md"
						key={index}
					>
						<GlowingBackground />
						<div className="glowing-div-content px-6 py-4 w-fullitems-center flex-1">
							<a href={item.post_metas.link} target="_blank" rel="noreferrer">
								<h1 className="flex items-center text-2xl tracking-wide font-medium mb-0.5">
									<Image
										alt={item.post_title}
										src={item.post_img.url}
										width={20}
										height={20}
										className="rounded-full border border-gray-200 dark:border-gray-500"
										loading="lazy"
									/>
									<span className="ml-2">{item.post_title}</span>
								</h1>
								<p
									className="text-4 text-gray-500 dark:text-gray-400 tracking-wide whitespace-nowrap overflow-hidden text-ellipsis"
									dangerouslySetInnerHTML={{
										__html: trimStr(item.post_excerpt.four, 150),
									}}
								/>
							</a>
						</div>
					</div>
				)
			})}
		</div>
	)
}

export default FriendsPage
