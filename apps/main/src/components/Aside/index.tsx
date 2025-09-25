import { Icon } from "@twilight-toolkit/ui"
import { debounce } from "lodash"
import Link from "next/link"
import { useRouter } from "next/router"
import { useEffect, useState } from "react"
import scrollToItemWithinDiv from "~/utilities/scrollTo"

export default function Aside({ preNext }: { preNext: any }) {
	const _router = useRouter()
	const [headersResult, setHeadersResult] = useState<any>([])
	const [headersEl, setHeadersEl] = useState<any>([])

	const scrollToItemWithinDivDebounced = debounce(scrollToItemWithinDiv, 200)

	/**
	 * Get all headers
	 */
	const getAllHeaders = () => {
		const result: any = [[], []]
		const headerElements: any = []

		const toc: any = document.querySelector("#toc")
			? document.querySelector("#toc").getElementsByTagName("li")
			: []

		for (let i = 0, n = toc.length; i < n; i++) {
			toc[i].classList.remove("toc-active")
		}

		const headers: any = document
			.querySelector(".prose")
			.getElementsByTagName("*")

		let minLevel: number

		for (let i = 0, n: number = headers.length; i < n; i++) {
			if (
				/^h\d{1}$/gi.test(headers[i].nodeName) &&
				headers[i].parentElement.className !== "embed-content"
			) {
				const headerLevel: number = parseInt(
					headers[i].tagName.substring(1, 2),
					10
				)
				const headerOffset: number = headers[i].offsetTop
				const headerContent: string = headers[i].innerText

				if (!minLevel || headerLevel <= minLevel) {
					minLevel = headerLevel
				}

				result[0].push([result[0].length, headerLevel, headerContent])
				result[1].push(headerOffset)
				headerElements.push(headers[i])
			}
		}

		for (let i = 0, n: number = result[0].length; i < n; i++) {
			result[0][i] = [
				result[0][i][0],
				(result[0][i][1] - minLevel) * 10,
				result[0][i][2],
			]
		}

		return [result, headerElements]
	}

	/**
	 * Scroll heading into view
	 * @param el heading DOM Element
	 */
	const scrollToHeading = (el: Element) => {
		const elY = el.getBoundingClientRect().top + window.pageYOffset - 75
		window.scrollTo({ top: elY, behavior: "smooth" })
	}

	useEffect(() => {
		const [result, elements] = getAllHeaders()
		setHeadersResult(result[0])
		setHeadersEl(elements)

		let currentHeaderId = 1
		let currentHeaderOffset = result[1][1]
		let lastHeaderOffset = result[1][0]

		/**
		 * Scroll event handler
		 */
		const scrollHandler = () => {
			const scrollPosition = window.pageYOffset - 250
			const listDiv = document.getElementById("toc")

			const firstHeader = document.getElementById(`header0`)
			const currentHeader = document.getElementById(`header${currentHeaderId}`)
			const prevHeader = document.getElementById(`header${currentHeaderId - 1}`)
			const prevPrevHeader = document.getElementById(
				`header${currentHeaderId - 2}`
			)

			if (scrollPosition >= currentHeaderOffset) {
				prevHeader?.classList.remove("toc-active")
				currentHeader?.classList.add("toc-active")
				if (currentHeader) {
					scrollToItemWithinDivDebounced(listDiv, currentHeader)
				}
				lastHeaderOffset = currentHeaderOffset
				currentHeaderId += 1
				currentHeaderOffset = result[1][currentHeaderId]
			} else if (scrollPosition < lastHeaderOffset) {
				if (currentHeaderId - 2 >= 0) {
					prevHeader?.classList.remove("toc-active")
					prevPrevHeader?.classList.add("toc-active")
					if (prevPrevHeader) {
						scrollToItemWithinDivDebounced(listDiv, prevPrevHeader)
					}
					currentHeaderId -= 1
					lastHeaderOffset = result[1][currentHeaderId - 1]
					currentHeaderOffset = result[1][currentHeaderId]
				} else {
					firstHeader?.classList.remove("toc-active")
					currentHeaderId = 1
					currentHeaderOffset = result[1][1]
					lastHeaderOffset = result[1][0]
				}
			} else if (scrollPosition > lastHeaderOffset && currentHeaderId === 1) {
				firstHeader?.classList.add("toc-active")
				if (firstHeader) {
					scrollToItemWithinDivDebounced(listDiv, firstHeader)
				}
			}
		}

		if (elements.length) {
			window.addEventListener("scroll", scrollHandler)
		}
		return () => {
			window.removeEventListener("scroll", scrollHandler)
		}
	}, [getAllHeaders, scrollToItemWithinDivDebounced])

	const SubItem = ({
		item,
		inner,
		recursionTimes,
	}: {
		item: any
		inner: boolean
		recursionTimes: number
	}) => {
		if (inner) {
			return (
				<div
					className={`${
						recursionTimes === 0 ? "border-l-0" : ""
					}toc-sub -my-1 cursor-pointer overflow-hidden text-ellipsis whitespace-nowrap border-gray-100 py-2 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700`}
					style={{
						paddingLeft: recursionTimes === 0 ? "0px" : "10px",
						marginLeft: recursionTimes === 0 ? "0px" : "10px",
					}}>
					{recursionTimes > 0 ? (
						<SubItem
							item={item}
							inner={true}
							recursionTimes={recursionTimes - 1}
						/>
					) : (
						item[2]
					)}
				</div>
			)
		} else {
			return (
				<li
					className={`${
						item[1] !== 0
							? "toc-sub hover:rounded-tl-none hover:rounded-bl-none"
							: ""
					} cursor-pointer overflow-hidden text-ellipsis whitespace-nowrap border-gray-100 py-2 pr-[10px] hover:rounded-md hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700`}
					id={`header${item[0]}`}
					style={{
						paddingLeft: "10px",
						marginLeft: item[1] !== 0 ? `10px` : "0px",
					}}
					key={item[0]}
					onClick={() => scrollToHeading(headersEl[item[0]])}
					data-oa="click-tocItem">
					{recursionTimes > 0 ? (
						<SubItem
							item={item}
							inner={true}
							recursionTimes={recursionTimes - 1}
						/>
					) : (
						item[2]
					)}
				</li>
			)
		}
	}

	const Tour = () => {
		const b =
			preNext.next[0] && [58, 5, 2, 3, 335, 74].indexOf(preNext.next[2]) === -1
		const a =
			preNext.prev[0] && [58, 5, 2, 3, 335, 74].indexOf(preNext.prev[2]) === -1
		if (a || b) {
			return (
				<div
					className={`mt-5 grid rounded-xl border bg-white text-gray-700 text-xl shadow-xs dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400 ${
						a && b ? "grid-cols-2" : "grid-cols-1"
					} tour`}>
					{a && (
						<Link href={`/post/${preNext.prev[0]}`} passHref>
							<div
								className={`flex cursor-pointer items-center justify-center px-6 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 ${
									b ? "rounded-tl-xl rounded-bl-xl" : "rounded-xl"
								}`}>
								<span className="mr-2 h-6 w-6">
									<Icon name="leftPlain" />
								</span>
								Prev
							</div>
						</Link>
					)}
					{b && (
						<Link href={`/post/${preNext.next[0]}`} passHref>
							<div
								className={`flex cursor-pointer items-center justify-center px-6 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 ${
									a ? "rounded-tr-xl rounded-br-xl" : "rounded-xl"
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
		} else {
			return <div />
		}
	}

	return (
		<aside className="aside group -ml-56 fixed top-24 hidden w-toc xl:block">
			{headersEl.length ? (
				<div>
					<div className="rounded-xl border bg-white shadow-xs dark:border-gray-800 dark:bg-gray-800">
						<h1 className="flex items-center border-gray-200 border-b px-6 py-3 font-medium text-2xl text-gray-700 tracking-wide dark:border-gray-700 dark:text-white">
							<span className="-mt-[1.5px] mr-2 h-[19px] w-[19px]">
								<Icon name="toc" />
							</span>
							On This Page
						</h1>
						<ul
							className="mask-y max-h-[70vh] overflow-hidden overflow-y-auto overscroll-contain px-3 py-3 text-gray-500 text-xl transition-colors duration-300 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300"
							id="toc">
							{headersResult?.map((item, index) => {
								return (
									<SubItem
										key={index}
										item={item}
										inner={false}
										recursionTimes={item[1] / 10}
									/>
								)
							})}
						</ul>
					</div>
					<Tour />
				</div>
			) : (
				""
			)}
		</aside>
	)
}
