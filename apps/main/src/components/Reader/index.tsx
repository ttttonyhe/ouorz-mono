import { Icon } from "@twilight-toolkit/ui"
import { MDXRemote } from "next-mdx-remote"
import { useCallback, useEffect } from "react"
import TimeAgo from "react-timeago"

import { useBodyScroll, useDispatch, useSelector } from "~/hooks"
import useLiveViews from "~/hooks/useLiveViews"
import useMdxPreview from "~/hooks/useMdxPreview"
import { hideReaderRequest } from "~/store/reader/actions"
import { selectReader } from "~/store/reader/selectors"

import PostContent from "../PostContent"

export default function Reader() {
	const [_bodyScrollable, setBodyScrollable] = useBodyScroll()
	const { animation, visible, postData } = useSelector(selectReader)
	const dispatch = useDispatch()
	const { mdxSource, isLoading: isPreviewLoading } = useMdxPreview(
		postData?.id,
		postData?.content.raw,
		visible
	)
	const { views: postViews, isLoading: isViewsLoading } = useLiveViews(
		"post",
		visible ? postData?.id : undefined,
		postData?.post_metas.views ?? 0
	)

	const dismissReader = useCallback(() => {
		if (animation === "out") return
		dispatch(hideReaderRequest())
	}, [animation, dispatch])

	useEffect(() => {
		setBodyScrollable(!visible)
	}, [visible, setBodyScrollable])

	useEffect(() => {
		if (!visible) return

		const onKeyDown = (event: KeyboardEvent) => {
			if (event.key === "Escape") {
				dismissReader()
			}
		}

		window.addEventListener("keydown", onKeyDown)
		return () => {
			window.removeEventListener("keydown", onKeyDown)
		}
	}, [dismissReader, visible])

	return (
		visible && (
			<div>
				<div
					className={`reader-bg z-50 ${
						animation === "in" ? "animate-reader-bg" : "animate-reader-bg-out"
					}`}
					onClick={dismissReader}
				/>
				<div
					className={`reader fixed top-0 z-60 mx-auto mt-20 ml-reader-offset w-page overflow-hidden overflow-y-auto rounded-xl bg-white px-20 py-16 shadow-md dark:border-gray-800 dark:bg-gray-800 ${
						animation === "in" ? "animate-reader" : "animate-reader-out"
					}`}>
					<button
						type="button"
						onClick={dismissReader}
						className="effect-pressing absolute top-8 right-8 flex items-center gap-x-1 rounded-md bg-gray-100 px-2 py-1 text-5 tracking-wide text-gray-500 transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
						<span className="h-4 w-4 rotate-180">
							<Icon name="right" />
						</span>
						Close
					</button>
					<h1 className="text-post-title leading-snug font-medium tracking-wider">
						{postData.title.rendered}
					</h1>
					<p className="mt-2 mb-16 flex space-x-2 text-xl tracking-wide text-gray-500">
						<span>
							Posted <TimeAgo date={postData.date} />
						</span>
						<span>·</span>
						{isViewsLoading ? (
							<span className="mt-0.5 inline-block h-6 w-16 animate-pulse rounded bg-gray-200 align-middle dark:bg-gray-600" />
						) : (
							<span>{postViews} Views</span>
						)}
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
					{isPreviewLoading ? (
						<div className="space-y-3">
							<div className="h-5 w-11/12 animate-pulse rounded bg-gray-200 dark:bg-gray-700" />
							<div className="h-5 w-10/12 animate-pulse rounded bg-gray-200 dark:bg-gray-700" />
							<div className="h-5 w-8/12 animate-pulse rounded bg-gray-200 dark:bg-gray-700" />
						</div>
					) : mdxSource ? (
						<div className="prose max-w-none tracking-wide dark:prose-invert">
							<MDXRemote {...mdxSource} />
						</div>
					) : (
						<PostContent content={postData.content.rendered} />
					)}
				</div>
			</div>
		)
	)
}
