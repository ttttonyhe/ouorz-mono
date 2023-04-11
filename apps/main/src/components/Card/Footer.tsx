import { useEffect, useState } from 'react'
import { Icon } from '@twilight-toolkit/ui'
import TimeAgo from 'react-timeago'
import { WPPost } from '~/constants/propTypes'
import useAnalytics from '~/hooks/analytics'

export default function CardFooter({ item }: { item: WPPost }) {
	const { trackEvent } = useAnalytics()
	const [canShare, setCanShare] = useState<boolean | undefined>()

	const doShare = async () => {
		try {
			await navigator.share({
				title: item.post_title,
				url: `${window.location.origin}/post/${item.id}`,
			})
			trackEvent('sharePost', 'click')
		} catch (err) {
			console.error('Failed to share:', err.message)
		}
	}

	useEffect(() => {
		setCanShare(!!navigator.share)
	}, [])

	return (
		<div className="py-3 px-5 lg:py-2 lg:px-10 items-center w-full h-auto border-t rounded-br-md rounded-bl-md border-gray-100 dark:border-gray-700">
			<p
				className={`flex justify-between items-center text-5 lg:text-4 tracking-wide leading-2 lg:leading-8 text-gray-500 dark:text-gray-400 whitespace-nowrap ${
					canShare === false ? 'animate-appear' : ''
				}`}
			>
				<span className="flex gap-x-2 items-center">
					<span>
						Posted <TimeAgo date={item.date} />
					</span>
					<span>·</span>
					{!canShare && (
						<>
							<span>{item.post_metas.views} Views</span>
							<span>·</span>
						</>
					)}
					<span>
						<abbr title="Estimated reading time">
							ERT {item.post_metas.reading.time_required} min
						</abbr>
					</span>
				</span>
				{canShare && (
					<span className="flex gap-x-2 items-center">
						<span>{item.post_metas.views} Views</span>
						<span>·</span>
						<button
							className="effect-pressing flex gap-x-1 items-center hover:text-gray-600 dark:hover:text-gray-300"
							onClick={doShare}
						>
							<span className="w-[15px] h-[15px]">
								<Icon name="share" />
							</span>
							Share
						</button>
					</span>
				)}
			</p>
		</div>
	)
}
