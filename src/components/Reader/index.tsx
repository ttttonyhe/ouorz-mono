import PostContent from '../PostContent'
import { useEffect } from 'react'
import TimeAgo from 'react-timeago'
import { useBodyScroll } from '~/hooks'
import { useSelector, useDispatch } from '~/hooks'
import { selectReader } from '~/store/selectors/reader'
import { readerActions } from '~/store/actions'

export default function Reader() {
	const [_, setBodyScrollable] = useBodyScroll()
	const { idle, visible, postData } = useSelector(selectReader)
	const dispatch = useDispatch()

	useEffect(() => {
		setBodyScrollable(!visible && idle)
	}, [visible, idle, setBodyScrollable])

	return (
		!idle && (
			<div>
				<div
					className={`z-10 reader-bg ${
						visible ? 'animate-readerBg' : 'animate-readerBgOut'
					}`}
					onClick={() => {
						dispatch(readerActions.toggle())
						setTimeout(() => {
							dispatch(readerActions.idle())
						}, 500)
					}}
				/>
				<div
					className={`z-20 fixed bg-white dark:bg-gray-800 dark:border-gray-800 shadow-md reader overflow-y-auto overflow-hidden rounded-tl-xl rounded-tr-xl px-20 py-16 w-page mx-auto top-0 mt-20 ml-readerOffset ${
						visible ? 'animate-reader' : 'animate-readerOut'
					}`}
				>
					<h1 className="text-postTitle font-medium tracking-wider leading-snug">
						{postData.title.rendered}
					</h1>
					<p className="flex text-xl text-gray-500 space-x-2 mt-2 tracking-wide mb-16">
						<span>
							Posted <TimeAgo date={postData.date} />
						</span>
						<span>·</span>
						<span>{postData.post_metas.views} Views</span>
						<span>·</span>
						<span className="group">
							<span className="group-hover:hidden">
								{postData.post_metas.reading.word_count} Words
							</span>
							<span className="hidden group-hover:block">
								<abbr title="Estimated reading time">
									ERT {postData.post_metas.reading.time_required} min
								</abbr>
							</span>
						</span>
					</p>
					<PostContent content={postData.content.rendered} />
				</div>
			</div>
		)
	)
}
