import React from 'react'
import TimeAgo from 'react-timeago'
import { Icon } from '@twilight-toolkit/ui'
import getApi from '~/utilities/api'
import { useDebounce } from '~/hooks'
import { WPPost } from '~/constants/propTypes'

interface Props {
	item: WPPost
	sticky: boolean
}

export default function CardPlainText({ item }: Props) {
	const [upvoting, setUpvoting] = React.useState<boolean>(false)
	const [upvotes, setUpvotes] = React.useState<number>(
		item.post_metas.markCount
	)

	/**
	 * Upvote the post
	 *
	 * @param {number} id postid
	 */
	const upvote = async (id: number) => {
		setUpvoting(true)
		await fetch(getApi({ mark: id }))
			.then(() => {
				setTimeout(() => {
					setUpvoting(false)
					setUpvotes((prev) => prev + 1)
				}, 500)
			})
			.catch(() => {
				setUpvoting(false)
			})
	}

	const doUpvote = useDebounce(upvote, 2000)

	return (
		<div className="w-full shadow-sm bg-white dark:bg-gray-800 dark:border-gray-800 rounded-md border mb-6">
			<div className="px-5 py-5 lg:px-10 lg:py-9">
				<h1
					className="font-normal text-2 lg:text-3xl text-gray-600 dark:text-white tracking-wider leading-2 lg:leading-10"
					dangerouslySetInnerHTML={{ __html: item.post_title }}
				/>
			</div>
			<div className="pt-3 pb-3 px-5 lg:pt-2 lg:pb-2 lg:px-10 items-center w-full h-auto border-t rounded-br-md rounded-bl-md border-gray-100 dark:border-gray-700">
				<p className="flex space-x-2 text-5 lg:text-4 tracking-wide leading-2 lg:leading-8 text-gray-500 dark:text-gray-400 items-center">
					<button
						className="flex items-center space-x-1 text-red-400 hover:text-red-500 cursor-pointer rounded-md"
						onClick={() => {
							if (!upvoting) {
								doUpvote(item.id)
							}
						}}
					>
						{upvoting ? (
							<i className="w-6 h-6 mt-1 animate-bounce">
								<Icon name="loveFill" />
							</i>
						) : (
							<i className="w-6 h-6 -mt-1">
								<Icon name="love" />
							</i>
						)}
						<em className={`not-italic ${!upvoting ? 'animate-appear' : ''}`}>
							{upvotes}
						</em>
					</button>
					<span className="lg:block hidden">·</span>
					<span className="lg:block hidden">
						Posted <TimeAgo date={item.date} />
					</span>
					<span>·</span>
					<span
						dangerouslySetInnerHTML={{
							__html: item.post_metas.status,
						}}
					/>
				</p>
			</div>
		</div>
	)
}
