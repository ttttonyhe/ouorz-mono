import { useState, useLayoutEffect } from 'react'
import { Icon } from '@twilight-toolkit/ui'
import { usePathname } from 'next/navigation'
import Link from 'next/link'
import scrollToItemWithinDiv from '~/utilities/scrollTo'

export default function Aside({ preNext }: { preNext: any }) {
	const pathname = usePathname()
	const [headersResult, setHeadersResult] = useState<any>([])
	const [headersEl, setHeadersEl] = useState<any>([])

	/**
	 * Get all headers
	 */
	const getAllHeaders = () => {
		const result: any = [[], []]
		const headerElements: any = []

		const toc: any = document.querySelector('#toc')
			? document.querySelector('#toc').getElementsByTagName('li')
			: []

		for (let i = 0, n = toc.length; i < n; i++) {
			toc[i].classList.remove('toc-active')
		}

		const headers: any = document
			.querySelector('.prose')
			.getElementsByTagName('*')

		let minLevel: number

		for (let i = 0, n: number = headers.length; i < n; i++) {
			if (
				/^h\d{1}$/gi.test(headers[i].nodeName) &&
				headers[i].parentElement.className !== 'embed-content'
			) {
				const headerLevel: number = parseInt(headers[i].tagName.substring(1, 2))
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

		let currentHeaderId = 1
		let currentHeaderOffset = result[1][1]
		let lastHeaderOffset = result[1][0]

		/**
		 * Scroll event handler
		 */
		const handleScroll = () => {
			const scrollPosition = window.pageYOffset - 250
			const listDiv = document.getElementById('toc')

			if (scrollPosition >= currentHeaderOffset) {
				document
					.getElementById(`header${currentHeaderId - 1}`)
					.classList.remove('toc-active')
				document
					.getElementById(`header${currentHeaderId}`)
					.classList.add('toc-active')
				scrollToItemWithinDiv(
					listDiv,
					document.getElementById(`header${currentHeaderId}`)
				)
				lastHeaderOffset = currentHeaderOffset
				currentHeaderId += 1
				currentHeaderOffset = result[1][currentHeaderId]
			} else if (scrollPosition < lastHeaderOffset) {
				if (currentHeaderId - 2 >= 0) {
					document
						.getElementById(`header${currentHeaderId - 1}`)
						.classList.remove('toc-active')
					document
						.getElementById(`header${currentHeaderId - 2}`)
						.classList.add('toc-active')
					scrollToItemWithinDiv(
						listDiv,
						document.getElementById(`header${currentHeaderId - 2}`)
					)
					currentHeaderId -= 1
					lastHeaderOffset = result[1][currentHeaderId - 1]
					currentHeaderOffset = result[1][currentHeaderId]
				} else {
					document.getElementById(`header0`).classList.remove('toc-active')
					currentHeaderId = 1
					currentHeaderOffset = result[1][1]
					lastHeaderOffset = result[1][0]
				}
			} else if (scrollPosition > lastHeaderOffset && currentHeaderId === 1) {
				document.getElementById(`header0`).classList.add('toc-active')
				scrollToItemWithinDiv(listDiv, document.getElementById(`header0`))
			}
		}

		return [result, handleScroll, headerElements]
	}

	/**
	 * Scroll heading into view
	 * @param el heading DOM Elment
	 */
	const scrollToHeading = (el: Element) => {
		const elY = el.getBoundingClientRect().top + window.pageYOffset - 75
		window.scrollTo({ top: elY, behavior: 'smooth' })
	}

	useLayoutEffect(() => {
		const result = getAllHeaders()
		const handler = result[1]
		setHeadersResult(result[0][0])
		setHeadersEl(result[2])
		if (result[2].length) {
			window.addEventListener('scroll', handler)
		}
		return () => {
			window.removeEventListener('scroll', handler)
		}
	}, [pathname])

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
						recursionTimes == 0 ? 'border-l-0' : ''
					}toc-sub py-2 -my-2 whitespace-nowrap text-ellipsis overflow-hidden cursor-pointer border-gray-100 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700`}
					style={{
						paddingLeft: recursionTimes == 0 ? '0px' : '10px',
						marginLeft: recursionTimes == 0 ? '0px' : '10px',
					}}
				>
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
							? 'toc-sub hover:rounded-tl-none hover:rounded-bl-none'
							: ''
					} py-2 pr-[10px] whitespace-nowrap text-ellipsis overflow-hidden cursor-pointer border-gray-100 dark:border-gray-600 hover:bg-gray-50 hover:rounded-md dark:hover:bg-gray-700`}
					id={`header${item[0]}`}
					style={{
						paddingLeft: '10px',
						marginLeft: item[1] !== 0 ? `10px` : '0px',
					}}
					key={item[0]}
					onClick={() => scrollToHeading(headersEl[item[0]])}
				>
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
			preNext['next'][0] && [58, 5, 2, 74].indexOf(preNext['next'][2]) === -1
		const a =
			preNext['prev'][0] && [58, 5, 2, 74].indexOf(preNext['prev'][2]) === -1
		if (a || b) {
			return (
				<div
					className={`bg-white text-gray-700 dark:bg-gray-800 dark:border-gray-800 dark:text-gray-400 shadow-sm border rounded-xl mt-5 text-xl grid ${
						a && b ? 'grid-cols-2' : 'grid-cols-1'
					} tour`}
				>
					{a && (
						<Link href={`/post/${preNext.prev[0]}`} passHref>
							<div
								className={`px-6 py-3 flex items-center justify-center cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 ${
									b ? 'rounded-tl-xl rounded-bl-xl' : 'rounded-xl'
								}`}
							>
								<span className="w-6 h-6 mr-2">
									<Icon name="leftPlain" />
								</span>
								Prev
							</div>
						</Link>
					)}
					{b && (
						<Link href={`/post/${preNext.next[0]}`} passHref>
							<div
								className={`px-6 py-3 flex items-center justify-center cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 ${
									a ? 'rounded-tr-xl rounded-br-xl' : 'rounded-xl'
								}`}
							>
								Next
								<span className="w-6 h-6 ml-2">
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
		<aside className="group w-toc fixed top-24 -ml-82 hidden xl:block aside">
			{headersEl.length ? (
				<div>
					<div className="shadow-sm border rounded-xl bg-white dark:bg-gray-800 dark:border-gray-800">
						<h1 className="flex text-2xl font-medium text-gray-700 dark:text-white tracking-wide items-center px-6 py-3 border-b border-gray-200 dark:border-gray-700">
							<span className="w-[19px] h-[19px] mr-2 -mt-[1.5px]">
								<Icon name="toc" />
							</span>
							Contents
						</h1>
						<ul
							className="max-h-[70vh] aside-mask overflow-hidden overflow-y-auto overscroll-contain text-xl px-3 py-3 transition-colors duration-300 text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300"
							id="toc"
						>
							{headersResult &&
								headersResult.map((item, index) => {
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
				''
			)}
		</aside>
	)
}
