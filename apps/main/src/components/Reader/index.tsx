import PostContent from "../PostContent"
import { useEffect } from "react"
import TimeAgo from "react-timeago"
import { useBodyScroll } from "~/hooks"
import { useSelector, useDispatch } from "~/hooks"
import { hideReaderRequest } from "~/store/reader/actions"
import { selectReader } from "~/store/reader/selectors"

export default function Reader() {
	const [_bodyScrollable, setBodyScrollable] = useBodyScroll()
	const { animation, visible, postData } = useSelector(selectReader)
	const dispatch = useDispatch()

	useEffect(() => {
		setBodyScrollable(!visible)
	}, [visible, setBodyScrollable])

	return (
		visible && (
			<div>
				<div
					className={`reader-bg z-50 ${
						animation === "in" ? "animate-reader-bg" : "animate-reader-bg-out"
					}`}
					onClick={() => {
						dispatch(hideReaderRequest())
					}}
				/>
				<div
					className={`reader fixed top-0 z-60 mx-auto ml-readerOffset mt-20 w-page overflow-hidden overflow-y-auto rounded-tl-xl rounded-tr-xl bg-white px-20 py-16 shadow-md dark:border-gray-800 dark:bg-gray-800 ${
						animation === "in" ? "animate-reader" : "animate-reader-out"
					}`}>
					<h1 className="text-postTitle font-medium leading-snug tracking-wider">
						{postData.title.rendered}
					</h1>
					<p className="mb-16 mt-2 flex space-x-2 text-xl tracking-wide text-gray-500">
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
