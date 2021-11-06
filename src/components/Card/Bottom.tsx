import TimeAgo from 'react-timeago'
export default function BottomCard({ item }: { item: any }) {
	return (
		<div className="py-3 px-5 lg:py-2 lg:px-10 items-center w-full h-auto border-t rounded-br-md rounded-bl-md border-gray-100 dark:border-gray-700">
			<p className="flex space-x-2 text-5 lg:text-4 tracking-wide leading-2 lg:leading-8 text-gray-500 dark:text-gray-400 whitespace-nowrap">
				<span>
					Posted <TimeAgo date={item.date} />
				</span>
				<span>·</span>
				<span>{item.post_metas.views} Views</span>
				<span>·</span>
				<span>
					<abbr title="Estimated reading time">
						ERT {item.post_metas.reading.time_required} min
					</abbr>
				</span>
			</p>
		</div>
	)
}
