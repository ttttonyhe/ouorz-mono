import TimeAgo from 'react-timeago'
import Icons from '~/components/Icons'
import { getApi } from '~/utilities/Api'
import React from 'react'

interface Props {
	item: any
	sticky: boolean
}

export default function CardPlainText({ item }: Props) {
	const [lastUpvoteTime, setLastUpvoteTime] = React.useState(0)
	const [upvoting, setUpvoting] = React.useState<boolean>(false)
	const [upvotes, setUpvotes] = React.useState<number>(
		item.post_metas.markCount
	)

	/**
	 * Upvote the post
	 *
	 * @param {number} id postid
	 */
	const doUpvote = async (id: number) => {
		if (lastUpvoteTime <= Date.now() - 2000) {
			setUpvoting(true)
			await fetch(getApi({ mark: id })).then(async (res: any) => {
				const data = await res.json()
				setUpvotes(data.markCountNow)
				setUpvoting(false)
				setLastUpvoteTime(Date.now())
			})
		}
	}

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
					<span
						className="flex items-center space-x-1 text-red-400 hover:text-red-500 cursor-pointer rounded-md"
						onClick={() => {
							if (!upvoting) {
								doUpvote(item.id)
							}
						}}
					>
						{upvoting ? (
							<i className="w-6 h-6 mt-1 animate-bounce">{Icons.loveFill}</i>
						) : (
							<i className="w-6 h-6 -mt-1">{Icons.love}</i>
						)}
						<em className="not-italic">{upvotes}</em>
					</span>
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
