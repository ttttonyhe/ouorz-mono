import { Icon } from "@twilight-toolkit/ui"
import React from "react"
import TimeAgo from "react-timeago"
import type { WPPost } from "~/constants/propTypes"
import { useDebouncedFunction } from "~/hooks"
import getAPI from "~/utilities/api"

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
		await fetch(getAPI("internal", "like", { id }))
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

	const doUpvote = useDebouncedFunction(upvote, 2000)

	return (
		<div className="mb-6 w-full rounded-md border bg-white shadow-xs dark:border-gray-800 dark:bg-gray-800">
			<div className="px-5 py-5 lg:px-10 lg:py-9">
				<h1
					className="font-normal text-2 text-gray-600 leading-2 tracking-wider lg:text-3xl lg:leading-10 dark:text-white"
					dangerouslySetInnerHTML={{ __html: item.post_title }}
				/>
			</div>
			<div className="h-auto w-full items-center rounded-br-md rounded-bl-md border-gray-100 border-t px-5 pt-3 pb-3 lg:px-10 lg:pt-2 lg:pb-2 dark:border-gray-700">
				<p className="flex items-center space-x-2 text-5 text-gray-500 leading-2 tracking-wide lg:text-4 lg:leading-8 dark:text-gray-400">
					<button
						className="flex cursor-pointer items-center space-x-1 rounded-md text-red-400 hover:text-red-500"
						onClick={() => {
							if (!upvoting) {
								doUpvote(item.id)
							}
						}}>
						{upvoting ? (
							<i className="mt-1 h-6 w-6 animate-bounce">
								<Icon name="loveFill" />
							</i>
						) : (
							<i className="-mt-1 h-6 w-6">
								<Icon name="love" />
							</i>
						)}
						<em className={`not-italic ${!upvoting ? "animate-appear" : ""}`}>
							{upvotes}
						</em>
					</button>
					<span className="hidden lg:block">·</span>
					<span className="hidden lg:block">
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
