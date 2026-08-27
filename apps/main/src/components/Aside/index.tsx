import { Icon } from "@twilight-toolkit/ui"
import { debounce } from "lodash"
import Link from "next/link"
import { useEffect, useMemo, useState } from "react"

import scrollToItemWithinDiv from "~/utilities/scrollTo"

/** A table-of-contents entry: its position, its indent level, and its text. */
type TocHeader = [index: number, indent: number, content: string]

type PreNextTuple = [number, string, number]

interface AsideProps {
	preNext: {
		prev: PreNextTuple | []
		next: PreNextTuple | []
	}
}

const EXCLUDED_CATEGORY_IDS = new Set([58, 5, 2, 3, 335, 74])
const HEADING_TAG = /^h\d$/iu

/** The post headings, in document order, excluding embedded content. */
const getHeadingElements = (): HTMLElement[] => {
	const proseRoot = document.querySelector(".prose")
	if (!proseRoot) return []

	return [...proseRoot.querySelectorAll<HTMLElement>("*")].filter(
		(element) =>
			HEADING_TAG.test(element.nodeName) &&
			element.parentElement?.className !== "embed-content"
	)
}

const collectHeaders = (): [TocHeader[], number[]] => {
	const elements = getHeadingElements()
	if (elements.length === 0) return [[], []]

	for (const item of document.querySelectorAll("#toc li")) {
		item.classList.remove("toc-active")
	}

	const levels = elements.map((element) => Number(element.tagName.slice(1, 2)))
	const minLevel = Math.min(...levels)

	return [
		elements.map((element, index) => [
			index,
			(levels[index] - minLevel) * 10,
			element.innerText,
		]),
		elements.map((element) => element.offsetTop),
	]
}

/**
 * Headings are looked up when they are needed rather than stored, because the
 * rendered post replaces its nodes once syntax highlighting finishes.
 */
const scrollToHeading = (index: number) => {
	const element = getHeadingElements()[index]
	if (!element) return
	const top = element.getBoundingClientRect().top + window.scrollY - 75
	window.scrollTo({ top, behavior: "smooth" })
}

interface SubItemProps {
	item: TocHeader
	inner: boolean
	recursionTimes: number
	onSelect: (index: number) => void
}

const SubItem = ({ item, inner, recursionTimes, onSelect }: SubItemProps) => {
	const content =
		recursionTimes > 0 ? (
			<SubItem
				item={item}
				inner={true}
				recursionTimes={recursionTimes - 1}
				onSelect={onSelect}
			/>
		) : (
			item[2]
		)

	if (inner) {
		return (
			<div
				className={`${
					recursionTimes === 0 ? "border-l-0 " : ""
				}toc-sub -my-1 cursor-pointer overflow-hidden border-gray-100 py-2 text-ellipsis whitespace-nowrap hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700`}
				style={{
					paddingLeft: recursionTimes === 0 ? "0px" : "10px",
					marginLeft: recursionTimes === 0 ? "0px" : "10px",
				}}>
				{content}
			</div>
		)
	}

	return (
		<li
			className={`${
				item[1] === 0
					? ""
					: "toc-sub hover:rounded-tl-none hover:rounded-bl-none"
			} cursor-pointer overflow-hidden border-gray-100 py-2 pr-[10px] text-ellipsis whitespace-nowrap hover:rounded-md hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700`}
			id={`header${item[0]}`}
			style={{
				paddingLeft: "10px",
				marginLeft: item[1] === 0 ? "0px" : "10px",
			}}
			onClick={() => onSelect(item[0])}
			data-oa="click-tocItem">
			{content}
		</li>
	)
}

const Tour = ({ preNext }: AsideProps) => {
	const hasNext =
		Boolean(preNext.next[0]) && !EXCLUDED_CATEGORY_IDS.has(preNext.next[2])
	const hasPrev =
		Boolean(preNext.prev[0]) && !EXCLUDED_CATEGORY_IDS.has(preNext.prev[2])

	if (!hasPrev && !hasNext) return <div />

	return (
		<div
			className={`mt-5 grid rounded-xl border bg-white text-xl text-gray-700 shadow-xs dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400 ${
				hasPrev && hasNext ? "grid-cols-2" : "grid-cols-1"
			} tour`}>
			{hasPrev && (
				<Link href={`/post/${preNext.prev[0]}`} passHref>
					<div
						className={`flex cursor-pointer items-center justify-center px-6 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 ${
							hasNext ? "rounded-tl-xl rounded-bl-xl" : "rounded-xl"
						}`}>
						<span className="mr-2 h-6 w-6">
							<Icon name="leftPlain" />
						</span>
						Prev
					</div>
				</Link>
			)}
			{hasNext && (
				<Link href={`/post/${preNext.next[0]}`} passHref>
					<div
						className={`flex cursor-pointer items-center justify-center px-6 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 ${
							hasPrev ? "rounded-tr-xl rounded-br-xl" : "rounded-xl"
						}`}>
						Next
						<span className="ml-2 h-6 w-6">
							<Icon name="right" />
						</span>
					</div>
				</Link>
			)}
		</div>
	)
}

export default function Aside({ preNext }: AsideProps) {
	const [headers, setHeaders] = useState<TocHeader[]>([])

	const scrollToItemWithinDivDebounced = useMemo(
		() => debounce(scrollToItemWithinDiv, 200),
		[]
	)

	useEffect(() => {
		const [collected, offsets] = collectHeaders()
		// oxlint-disable-next-line react/set-state-in-effect -- the table of contents can only be measured from the rendered post
		setHeaders(collected)

		let currentHeaderId = 1
		let currentHeaderOffset = offsets[1]
		let lastHeaderOffset = offsets[0]

		const scrollHandler = () => {
			const scrollPosition = window.scrollY - 250
			const listDiv = document.querySelector("#toc")

			const firstHeader = document.querySelector("#header0")
			const currentHeader = document.querySelector(`#header${currentHeaderId}`)
			const prevHeader = document.querySelector(`#header${currentHeaderId - 1}`)
			const prevPrevHeader = document.querySelector(
				`#header${currentHeaderId - 2}`
			)

			if (scrollPosition >= currentHeaderOffset) {
				prevHeader?.classList.remove("toc-active")
				currentHeader?.classList.add("toc-active")
				if (currentHeader) {
					scrollToItemWithinDivDebounced(listDiv, currentHeader)
				}
				lastHeaderOffset = currentHeaderOffset
				currentHeaderId += 1
				currentHeaderOffset = offsets[currentHeaderId]
			} else if (scrollPosition < lastHeaderOffset) {
				if (currentHeaderId - 2 >= 0) {
					prevHeader?.classList.remove("toc-active")
					prevPrevHeader?.classList.add("toc-active")
					if (prevPrevHeader) {
						scrollToItemWithinDivDebounced(listDiv, prevPrevHeader)
					}
					currentHeaderId -= 1
					lastHeaderOffset = offsets[currentHeaderId - 1]
					currentHeaderOffset = offsets[currentHeaderId]
				} else {
					firstHeader?.classList.remove("toc-active")
					currentHeaderId = 1
					currentHeaderOffset = offsets[1]
					lastHeaderOffset = offsets[0]
				}
			} else if (scrollPosition > lastHeaderOffset && currentHeaderId === 1) {
				firstHeader?.classList.add("toc-active")
				if (firstHeader) {
					scrollToItemWithinDivDebounced(listDiv, firstHeader)
				}
			}
		}

		if (collected.length > 0) {
			window.addEventListener("scroll", scrollHandler)
		}
		return () => {
			window.removeEventListener("scroll", scrollHandler)
			scrollToItemWithinDivDebounced.cancel()
		}
	}, [scrollToItemWithinDivDebounced])

	if (headers.length === 0) return null

	return (
		<aside className="aside group fixed top-24 -ml-56 hidden w-toc xl:block">
			<div>
				<div className="rounded-xl border bg-white shadow-xs dark:border-gray-800 dark:bg-gray-800">
					<h1 className="flex items-center border-b border-gray-200 px-6 py-3 text-2xl font-medium tracking-wide text-gray-700 dark:border-gray-700 dark:text-white">
						<span className="-mt-[1.5px] mr-2 h-[19px] w-[19px]">
							<Icon name="toc" />
						</span>
						On This Page
					</h1>
					<ul
						className="mask-y max-h-[70vh] overflow-hidden overflow-y-auto overscroll-contain px-3 py-3 text-xl text-gray-500 transition-colors duration-300 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300"
						id="toc">
						{headers.map((item) => (
							<SubItem
								key={item[0]}
								item={item}
								inner={false}
								recursionTimes={item[1] / 10}
								onSelect={scrollToHeading}
							/>
						))}
					</ul>
				</div>
				<Tour preNext={preNext} />
			</div>
		</aside>
	)
}
