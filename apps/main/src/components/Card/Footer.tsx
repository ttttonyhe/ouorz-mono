import { Icon } from "@twilight-toolkit/ui"
import { useEffect, useState } from "react"
import TimeAgo from "react-timeago"
import { WPPost } from "~/constants/propTypes"
import useAnalytics from "~/hooks/analytics"

export default function CardFooter({ item }: { item: WPPost }) {
	const { trackEvent } = useAnalytics()
	const [canShare, setCanShare] = useState<boolean | undefined>()

	const doShare = async () => {
		try {
			await navigator.share({
				title: item.post_title,
				url: `${window.location.origin}/post/${item.id}`,
			})
			trackEvent("sharePost", "click")
		} catch (err) {
			console.error("Failed to share:", err.message)
		}
	}

	useEffect(() => {
		setCanShare(!!navigator.share)
	}, [])

	return (
		<div className="h-auto w-full items-center rounded-br-md rounded-bl-md border-t border-gray-100 px-5 py-3 lg:px-10 lg:py-2 dark:border-gray-700">
			<p
				className={`text-5 lg:text-4 flex items-center justify-between leading-2 tracking-wide whitespace-nowrap text-gray-500 lg:leading-8 dark:text-gray-400 ${
					canShare === false ? "animate-appear" : ""
				}`}>
				<span className="flex items-center gap-x-2">
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
					<span className="flex items-center gap-x-2">
						<span>{item.post_metas.views} Views</span>
						<span>·</span>
						<button
							className="effect-pressing flex items-center gap-x-1 hover:text-gray-600 dark:hover:text-gray-300"
							onClick={doShare}>
							<span className="h-[15px] w-[15px]">
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
