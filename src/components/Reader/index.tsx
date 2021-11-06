import PostContent from '../PostContent'
import { useEffect } from 'react'
import TimeAgo from 'react-timeago'

export default function Reader({
	data,
	setReader,
}: {
	data: { status: boolean; post: any }
	setReader: any
}) {
	if (data.status) {
		useEffect(() => {
			document.getElementsByTagName('body')[0].classList.add('stop-scrolling')
		}, [])
	}
	return (
		data.post.length !== 0 && (
			<div>
				<div
					className={`z-10 reader-bg ${
						data.status ? 'animate-readerBg' : 'animate-readerBgOut'
					}`}
					onClick={() => {
						setReader({ status: false, post: data.post })
						setTimeout(() => {
							setReader({ status: false, post: [] })
						}, 500)
						document
							.getElementsByTagName('body')[0]
							.classList.remove('stop-scrolling')
					}}
				/>
				<div
					className={`z-20 fixed bg-white dark:bg-gray-800 dark:border-gray-800 shadow-md reader overflow-y-auto overflow-hidden rounded-tl-xl rounded-tr-xl px-20 py-16 w-page mx-auto top-0 mt-20 ml-readerOffset ${
						data.status ? 'animate-reader' : 'animate-readerOut'
					}`}
				>
					<h1 className="text-postTitle font-medium tracking-wider leading-snug">
						{data.post.title.rendered}
					</h1>
					<p className="flex text-xl text-gray-500 space-x-2 mt-2 tracking-wide mb-16">
						<span>
							Posted <TimeAgo date={data.post.date} />
						</span>
						<span>·</span>
						<span>{data.post.post_metas.views} Views</span>
						<span>·</span>
						<span className="group">
							<span className="group-hover:hidden">
								{data.post.post_metas.reading.word_count} Words
							</span>
							<span className="hidden group-hover:block">
								<abbr title="Estimated reading time">
									ERT {data.post.post_metas.reading.time_required} min
								</abbr>
							</span>
						</span>
					</p>
					<PostContent content={data.post.content.rendered} />
				</div>
			</div>
		)
	)
}
