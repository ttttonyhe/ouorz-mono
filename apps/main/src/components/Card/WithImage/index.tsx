import { Icon, Label, LabelGroup } from '@twilight-toolkit/ui'
import CardFooter from '~/components/Card/Footer'
import CardWithImageTool from '~/components/Card/WithImage/tool'
import trimStr from '~/utilities/trimString'
import Link from 'next/link'
import Image from 'next/image'
import CardWithImagePodcast from '~/components/Card/WithImage/podcast'
import { useDispatch } from '~/hooks'
import { setReaderRequest } from '~/store/reader/actions'
import { WPPost } from '~/constants/propTypes'
import { Hover } from '~/components/Visual'
import blurDataURL from '~/constants/blurDataURL'
import { useCallback, useState } from 'react'
import { useRouter } from 'next/router'
import useInterval from '~/hooks/useInterval'
import useAnalytics from '~/hooks/analytics'

interface Props {
	item: WPPost
	sticky: boolean
}

export default function CardWithImage({ item, sticky }: Props) {
	const { trackEvent } = useAnalytics()
	const dispatch = useDispatch()
	const router = useRouter()

	const [summarizing, setSummarizing] = useState<boolean>(false)
	const [summary, setSummary] = useState<string>('')
	const [outputText, setOutputText] = useState<string>('')
	const [outputing, setOutputing] = useState<boolean>(false)
	const [showThumbnail, setShowThumbnail] = useState<boolean>(true)

	const handleSummarize = useCallback(async () => {
		trackEvent('summarizePost', 'click')
		setSummarizing(true)

		try {
			const res = await fetch('api/summarize', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify({
					content: item.content.rendered,
				}),
			})

			const data = await res.json()

			setTimeout(() => {
				setOutputText(data.choices[0].text)
				setSummarizing(false)
				setTimeout(() => {
					setShowThumbnail(false)
				}, 500)
				setOutputing(true)
			}, 1000)
		} catch (e) {
			setSummarizing(false)
		}
	}, [])

	useInterval(
		() => {
			if (outputText.length === 0) {
				setOutputing(false)
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
		outputing ? 200 : null
	)

	const summarized = !summarizing && summary

	if (typeof item.post_metas.fineTool === 'undefined') {
		if (item.post_categories[0].term_id === 120) {
			return <CardWithImagePodcast item={item} sticky={sticky} />
		}

		return (
			<div className="w-full shadow-sm bg-white dark:bg-gray-800 dark:border-gray-800 rounded-md border mb-6">
				<div className="p-5 lg:p-10 lg:grid lg:grid-flow-col lg:grid-cols-3 lg:gap-9">
					<Hover
						perspective={1000}
						max={25}
						scale={1.01}
						className={`dark:opacity-90 relative overflow-hidden hidden rounded-md shadow-sm hover:shadow-md h-img min-h-full w-full col-span-1 col-end-2 border border-gray-200 transition-all ${
							showThumbnail ? 'lg:block' : 'lg:hidden'
						} ${summarized ? 'animate-shrinkDisappear' : ''}`}
					>
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
								? 'col-span-2'
								: 'col-span-3 animate-expandImageCardInfo ml-auto'
						}`}
					>
						<div className="flex space-x-3 items-center">
							<div className="flex space-x-2 col-start-1 col-end-3">
								{sticky && <Label type="sticky-icon" />}
								<Link href={`/cate/${item.post_categories[0].term_id}`}>
									<Label type="primary" icon="cate">
										{item.post_categories[0].name}
									</Label>
								</Link>
							</div>
							<div className="justify-end hidden lg:flex lg:w-auto w-full">
								<LabelGroup className="h-[33px]">
									<Label
										type="secondary"
										icon="preview"
										onClick={() => {
											trackEvent('previewPost', 'click')
											dispatch(setReaderRequest(item))
										}}
									>
										Preview
									</Label>
									{!summarizing && summary ? (
										<Label
											type="green-icon"
											icon="right"
											onClick={() => {
												router.push(`/post/${item.id}`)
											}}
										/>
									) : (
										<Label
											type="orange-icon"
											icon="openai"
											iconClassName={summarizing ? 'animate-spin' : ''}
											onClick={() => {
												if (!summarizing && !summary && !outputText) {
													handleSummarize()
												}
											}}
										/>
									)}
								</LabelGroup>
							</div>
						</div>
						{summary && !showThumbnail ? (
							<div className="lg:mt-4 mt-6 animate-appear">
								<Link href={`/post/${item.id}`}>
									<div className="flex mb-4 gap-x-2 flex-col border dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
										<h2 className="text-sm font-semibold dark:border-gray-600 dark:text-gray-300 text-gray-500 px-3.5 uppercase tracking-wide py-2 w-full border-b">
											TITLE
										</h2>
										<h1
											className="text-gray-500 dark:text-gray-400 text-4 px-3.5 py-1.5 lg:text-3 tracking-wide leading-2 lg:leading-7 overflow-hidden text-ellipsis"
											dangerouslySetInnerHTML={{ __html: item.post_title }}
										/>
									</div>
								</Link>
								<div className="flex mb-4 gap-x-2 flex-col border dark:border-gray-600 rounded-md">
									<h2 className="text-sm font-semibold dark:border-gray-600 dark:text-gray-300 text-gray-500 px-3.5 uppercase tracking-wide py-2 w-full border-b">
										TL;DR
									</h2>
									<p
										className="text-gray-500 dark:text-gray-400 text-4 px-3.5 py-1.5 lg:text-3 tracking-wide leading-2 lg:leading-7 overflow-hidden text-ellipsis"
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
										className="flex items-center gap-x-2 text-sm text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400 transition-colors"
									>
										<span className="ml-[3px] tracking-wide">Powered by</span>
										<span className="w-16">
											<Icon name="openaiText" />
										</span>
									</a>
								</div>
							</div>
						) : (
							<div className="lg:mt-4 mt-6">
								<Link href={`/post/${item.id}`}>
									<h1
										className="font-medium text-2 lg:text-listTitle text-gray-700 dark:text-white tracking-wider mb-5"
										dangerouslySetInnerHTML={{ __html: item.post_title }}
									/>
								</Link>
								<p
									className="text-gray-500 dark:text-gray-400 text-4 lg:text-3 tracking-wide leading-2 lg:leading-8 overflow-hidden text-ellipsis"
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
