import { Icon, Label } from "@twilight-toolkit/ui"
import Image from "next/image"
import Link from "next/link"
import { useRouter } from "next/router"
import { useCallback, useState } from "react"
import CardFooter from "~/components/Card/Footer"
import CardWithImagePodcast from "~/components/Card/WithImage/podcast"
import CardWithImageTool from "~/components/Card/WithImage/tool"
import { Hover } from "~/components/Visual"
import blurDataURL from "~/constants/blurDataURL"
import type { WPPost } from "~/constants/propTypes"
import { useDispatch } from "~/hooks"
import useAnalytics from "~/hooks/analytics"
import useInterval from "~/hooks/useInterval"
import { setReaderRequest } from "~/store/reader/actions"
import { trimStr } from "~/utilities/string"

interface Props {
	item: WPPost
	sticky: boolean
}

export default function CardWithImage({ item, sticky }: Props) {
	const { trackEvent } = useAnalytics()
	const dispatch = useDispatch()
	const router = useRouter()

	const [summarizing, setSummarizing] = useState<boolean>(false)
	const [summary, setSummary] = useState<string>("")
	const [outputText, setOutputText] = useState<string>("")
	const [outputting, setOutputting] = useState<boolean>(false)
	const [showThumbnail, setShowThumbnail] = useState<boolean>(true)
	const [summarizeError, setSummarizeError] = useState<string>("")

	const handleSummarize = useCallback(async () => {
		trackEvent("summarizePost", "click")
		setSummarizing(true)
		setSummarizeError("")

		try {
			const res = await fetch("/api/summarize", {
				method: "POST",
				headers: {
					"Content-Type": "application/json",
				},
				body: JSON.stringify({
					identifier: `posts/${item.id}`,
					content: item.content.rendered,
				}),
			})

			if (!res.ok) {
				setSummarizing(false)
				setSummarizeError("Failed to summarize")
				return
			}

			const data = await res.json()

			setTimeout(() => {
				const summaryText = data?.choices?.[0]?.text
				if (!summaryText) {
					setSummarizeError("No summary generated")
					setSummarizing(false)
					return
				}

				setOutputText(summaryText.replace(/^: /, ""))
				setSummarizing(false)
				setTimeout(() => {
					setShowThumbnail(false)
				}, 500)
				setOutputting(true)
			}, 1000)
		} catch (e) {
			setSummarizing(false)
			setSummarizeError("Failed to summarize")
			console.log(e)
		}
	}, [item.content.rendered, item.id, trackEvent])

	useInterval(
		() => {
			if (outputText.length === 0) {
				setOutputting(false)
				return
			}

			const increment = Math.floor(Math.random() * 10) + 1

			setSummary((prev) => {
				return prev + outputText.substring(0, increment)
			})
			setOutputText((prev) => {
				return prev.slice(increment)
			})
		},
		outputting ? 200 : null
	)

	const summarized = !summarizing && summary

	if (typeof item.post_metas.fineTool === "undefined") {
		if (item.post_categories[0].term_id === 120) {
			return <CardWithImagePodcast item={item} sticky={sticky} />
		}

		return (
			<div className="shadow-xs mb-6 w-full rounded-md border bg-white dark:border-gray-700 dark:bg-gray-800">
				<div className="p-5 lg:grid lg:grid-flow-col lg:grid-cols-3 lg:gap-9 lg:p-10">
					<Hover
						perspective={1000}
						max={25}
						scale={1.01}
						className={`shadow-xs relative col-span-1 col-end-2 hidden h-img min-h-full w-full overflow-hidden rounded-md border border-gray-200 transition-all hover:shadow-md dark:opacity-90 ${
							showThumbnail ? "lg:block" : "lg:hidden"
						} ${summarized ? "animate-shrink-disappear" : ""}`}>
						<Image
							fill
							src={item.post_img.url}
							placeholder="blur"
							blurDataURL={blurDataURL}
							className="rounded-md object-cover"
							alt={`featured-image-${item.post_title}`}
							loading="lazy"
						/>
					</Hover>
					<div
						className={`col-end-4 ${
							showThumbnail
								? "col-span-2"
								: "col-span-3 ml-auto animate-expand-image-card-info"
						}`}>
						<div className="flex items-center space-x-3">
							<div className="col-start-1 col-end-3 flex space-x-2">
								{sticky && <Label type="sticky-icon" />}
								<Link href={`/cate/${item.post_categories[0].term_id}`}>
									<Label type="primary" icon="cate">
										{item.post_categories[0].name}
									</Label>
								</Link>
							</div>
							<div className="hidden w-full justify-end lg:flex lg:w-auto">
								<div className="flex overflow-hidden rounded-md border border-gray-200 dark:border-gray-600">
									<button
										type="button"
										className="effect-pressing flex items-center rounded-l-sm bg-gray-100 px-2 py-1 text-4 font-medium tracking-wide text-gray-500 transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 lg:px-4 lg:py-1 lg:text-label"
										onClick={() => {
											trackEvent("previewPost", "click")
											dispatch(setReaderRequest(item))
										}}>
										<span className="mr-1 h-4 w-4 lg:mr-2 lg:h-7 lg:w-7">
											<Icon name="preview" />
										</span>
										Preview
									</button>
									{!summarizing && summary ? (
										<button
											type="button"
											className="effect-pressing flex animate-appear items-center justify-center border-l border-gray-200 bg-green-100 px-2 py-2 text-xl text-green-500 transition-colors hover:bg-green-200 dark:border-gray-600 dark:bg-green-700 dark:text-green-300 dark:hover:bg-green-600"
											onClick={() => {
												router.push(`/post/${item.id}`)
											}}>
											<span className="h-4 w-4 lg:h-[19px] lg:w-[19px]">
												<Icon name="right" />
											</span>
										</button>
									) : (
										<button
											type="button"
											className="effect-pressing flex items-center justify-center border-l border-gray-200 bg-orange-100 px-2 py-2 text-xl text-orange-500 transition-colors hover:bg-orange-200 dark:border-gray-600 dark:bg-orange-700 dark:text-orange-300 dark:hover:bg-orange-600"
											onClick={() => {
												if (!summarizing && !summary && !outputText) {
													handleSummarize()
												}
											}}>
											<span
												className={`h-4 w-4 lg:h-[19px] lg:w-[19px] ${
													summarizing ? "animate-spin" : ""
												}`}>
												<Icon name="openai" />
											</span>
										</button>
									)}
								</div>
							</div>
						</div>
						{summarizeError && (
							<p className="mt-2 text-5 tracking-wide text-red-500">
								{summarizeError}
							</p>
						)}
						{summary && !showThumbnail ? (
							<div className="mt-6 animate-appear lg:mt-4">
								<Link href={`/post/${item.id}`}>
									<div className="shadow-xs group mb-4 flex flex-col gap-x-2 rounded-md border transition-colors hover:bg-gray-50 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-700">
										<h2 className="flex w-full items-center justify-between gap-x-1 border-b px-3.5 py-2 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:border-gray-600 dark:text-gray-300 dark:group-hover:border-gray-500">
											TITLE
											<span className="-mr-2 h-4 w-4 opacity-0 transition-all group-hover:mr-0 group-hover:opacity-100">
												<Icon name="right" />
											</span>
										</h2>
										<h1
											className="leading-2 overflow-hidden text-ellipsis px-3.5 py-1.5 text-4 tracking-wide text-gray-500 dark:text-gray-400 lg:text-3 lg:leading-7"
											dangerouslySetInnerHTML={{ __html: item.post_title }}
										/>
									</div>
								</Link>
								<div className="shadow-xs mb-4 flex flex-col gap-x-2 rounded-md border dark:border-gray-600">
									<h2 className="w-full border-b px-3.5 py-2 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:border-gray-600 dark:text-gray-300">
										TL;DR
									</h2>
									<p
										className="leading-2 overflow-hidden text-ellipsis px-3.5 py-1.5 text-4 tracking-wide text-gray-500 dark:text-gray-400 lg:text-3 lg:leading-7"
										dangerouslySetInnerHTML={{
											__html: summary,
										}}
									/>
								</div>
								<div className="-mb-2">
									<a
										href="https://openai.com"
										target="_blank"
										rel="noopener noreferrer"
										className="flex items-center gap-x-2 text-sm text-gray-400 transition-colors hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400">
										<span className="ml-[3px] tracking-wide">Powered by</span>
										<span className="w-16">
											<Icon name="openaiText" />
										</span>
									</a>
								</div>
							</div>
						) : (
							<div className="mt-4 lg:mt-3.5">
								<Link href={`/post/${item.id}`}>
									<h1
										className="mb-2.5 text-2 font-medium tracking-wider text-gray-700 dark:text-white lg:text-list-title"
										dangerouslySetInnerHTML={{ __html: item.post_title }}
									/>
								</Link>
								<p
									className="overflow-hidden text-ellipsis text-4 tracking-wide text-gray-500 dark:text-gray-400 lg:text-3 lg:leading-8"
									dangerouslySetInnerHTML={{
										__html: trimStr(item.post_excerpt.four, 150),
									}}
								/>
							</div>
						)}
					</div>
				</div>
				<CardFooter item={item} />
			</div>
		)
	}

	return <CardWithImageTool item={item} sticky={sticky} />
}
