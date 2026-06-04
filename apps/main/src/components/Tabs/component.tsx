import type React from "react"
import { useCallback, useEffect, useRef, useState } from "react"
import { useHotkeys } from "react-hotkeys-hook"
import { useMouseLeaveListener } from "~/hooks"
import scrollToItemWithinDiv from "~/utilities/scrollTo"
import type { TabsProps } from "."
import TabItemComponent from "./item"

const Tabs = (props: TabsProps) => {
	const { items, direction, defaultHighlighted, verticalListWrapper } = props
	const wrapperRef = useRef<HTMLDivElement>(null)
	const highlighterRef = useRef<HTMLDivElement>(null)
	const listRef = useRef<HTMLUListElement>(null)
	const [highlightedIndex, setHighlightedIndex] = useState<number>(-1)
	const withinWrapperRef = useRef(false)
	const highlightedIndexRef = useRef(highlightedIndex)

	const updateWithinWrapper = useCallback((value: boolean) => {
		withinWrapperRef.current = value
	}, [])

	const updateHighlightedIndex = useCallback((value: number) => {
		highlightedIndexRef.current = value
		setHighlightedIndex(value)
	}, [])

	/* Begin Highlighting Methods */

	/**
	 * Change the styling of the highlighter
	 *
	 * @param {boolean} visible
	 * @param {(DOMRect | null)} [wrapperBoundingBox]
	 * @param {(DOMRect | null)} [tabBoundingBox]
	 * @param {string} [bgColor]
	 * @param {string} [bgDark]
	 * @param {string} [className]
	 */
	const styleHighlighter = useCallback(
		(
			visible: boolean,
			wrapperBoundingBox?: DOMRect | null,
			tabBoundingBox?: DOMRect | null,
			bgColor?: string,
			bgDark?: string,
			className?: string
		) => {
			if (!highlighterRef.current) return

			highlighterRef.current.style.transitionDuration =
				visible && (withinWrapperRef.current || highlightedIndexRef.current > 0)
					? "150ms"
					: "0ms"

			highlighterRef.current.style.opacity = visible ? "1" : "0"

			if (visible && tabBoundingBox) {
				highlighterRef.current.style.width =
					highlightedIndexRef.current === -1 &&
					defaultHighlighted &&
					direction === "vertical"
						? "100%"
						: `${tabBoundingBox.width}px`
			} else {
				highlighterRef.current.style.width = "0"
			}

			if (visible && tabBoundingBox) {
				highlighterRef.current.style.height =
					highlightedIndexRef.current === -1 &&
					defaultHighlighted &&
					direction === "vertical"
						? "46.3889px"
						: `${tabBoundingBox.height}px`
			} else {
				highlighterRef.current.style.height = "0"
			}

			if (visible && wrapperBoundingBox && tabBoundingBox) {
				highlighterRef.current.style.transform =
					direction === "vertical"
						? `translateY(${tabBoundingBox.top - wrapperBoundingBox.top}px)`
						: `translateX(${tabBoundingBox.left - wrapperBoundingBox.left}px)`
			} else {
				highlighterRef.current.style.transform = "none"
			}

			if (visible) {
				highlighterRef.current.className = [
					"tabs-highlighter z-0",
					className || "",
					bgColor || "bg-menu",
					bgDark || "dark:bg-gray-700/40 backdrop-blur-xs",
				].join(" ")
			} else {
				highlighterRef.current.className = ""
			}
		},
		[defaultHighlighted, direction]
	)

	const reset = useCallback(
		(skipVertical?: boolean) => {
			if (skipVertical && direction === "vertical") return
			updateWithinWrapper(false)
			styleHighlighter(false)
			updateHighlightedIndex(-1)
		},
		[direction, styleHighlighter, updateHighlightedIndex, updateWithinWrapper]
	)

	/**
	 * Highlight the tab that the mouse is currently hovering over
	 *
	 * @param {React.MouseEvent<HTMLElement>} e
	 * @param {string} [bgColor]
	 * @param {string} [bgDark]
	 * @param {string} [className] - class name to add to the highlighter
	 * @param {number} [index] - index of the tab to highlight
	 * @param {string} [from] - highlighter initial direction
	 */
	const highlight = useCallback(
		(
			e: React.MouseEvent<HTMLElement> | Element,
			bgColor?: string,
			bgDark?: string,
			className?: string,
			index = -1,
			from?: "above" | "below"
		) => {
			if (items[index]?.hoverable === false && e instanceof Element) {
				const targetIndex =
					from === "below"
						? index - 1 >= 0
							? index - 1
							: items.length - 1
						: index + 1 < items.length
							? index + 1
							: 0
				const targetElement = listRef.current?.children[targetIndex]

				if (targetElement && targetIndex >= 0 && targetIndex < items.length) {
					highlight(
						targetElement,
						items[targetIndex].bgColor,
						items[targetIndex].bgDark,
						className,
						targetIndex,
						from
					)
				}
				return
			}

			const targetListElement = listRef.current?.children[index]

			if (verticalListWrapper?.current && targetListElement) {
				scrollToItemWithinDiv(verticalListWrapper.current, targetListElement)
			}

			const targetTabBoundingBox =
				e instanceof Element
					? e.getBoundingClientRect()
					: e.currentTarget.getBoundingClientRect()
			const wrapperBoundingBox = wrapperRef.current?.getBoundingClientRect()
			styleHighlighter(
				true,
				wrapperBoundingBox,
				targetTabBoundingBox,
				bgColor,
				bgDark,
				className
			)
			updateWithinWrapper(true)
			index >= 0 && updateHighlightedIndex(index)
		},
		[
			items,
			styleHighlighter,
			updateHighlightedIndex,
			updateWithinWrapper,
			verticalListWrapper,
		]
	)

	/**
	 * Hightlight the first tab item when defaultHighlighted is true and direction
	 * is vertical
	 */
	const highlightFirstItem = useCallback(
		(delay: number) => {
			const timeout = window.setTimeout(() => {
				if (!listRef.current) return
				if (items[0] && direction === "vertical" && defaultHighlighted) {
					highlight(
						listRef.current.children[0],
						items[0].bgColor,
						items[0].bgDark,
						delay === 0 ? "" : "animate-kbar-highlighter",
						0,
						"above"
					)
				} else {
					reset()
				}
			}, delay)

			return () => {
				window.clearTimeout(timeout)
			}
		},
		[defaultHighlighted, direction, highlight, items, reset]
	)

	/* End Highlighting Methods */

	/* Begin Vertical List Methods */

	useHotkeys(
		"down",
		(e) => {
			if (direction !== "vertical") return
			e.preventDefault()
			const targetIndex =
				highlightedIndex + 1 < items.length ? highlightedIndex + 1 : 0
			const targetElement = listRef.current?.children[targetIndex]

			if (!targetElement) return

			highlight(
				targetElement,
				items[targetIndex].bgColor,
				items[targetIndex].bgDark,
				"",
				targetIndex,
				"above"
			)
		},
		{
			enableOnFormTags: ["INPUT"],
		},
		[direction, highlight, highlightedIndex, items]
	)
	useHotkeys(
		"up",
		(e) => {
			if (direction !== "vertical") return
			e.preventDefault()
			const targetIndex =
				highlightedIndex - 1 >= 0 ? highlightedIndex - 1 : items.length - 1
			const targetElement = listRef.current?.children[targetIndex]

			if (!targetElement) return

			highlight(
				targetElement,
				items[targetIndex].bgColor,
				items[targetIndex].bgDark,
				"",
				targetIndex,
				"below"
			)
		},
		{
			enableOnFormTags: ["INPUT"],
		},
		[direction, highlight, highlightedIndex, items]
	)
	useHotkeys(
		"enter",
		(e) => {
			if (direction !== "vertical") return
			e.preventDefault()
			items[highlightedIndex]?.onClick?.()
		},
		{
			enableOnFormTags: ["INPUT"],
		},
		[direction, highlightedIndex, items]
	)

	useEffect(() => {
		if (direction !== "vertical") return
		if (!listRef.current || !verticalListWrapper?.current) return
		const listHeight = listRef.current.getBoundingClientRect().height
		verticalListWrapper.current.style.height = `${
			listHeight >= 340 ? 360 : listHeight + 20
		}px`
	}, [direction, items, verticalListWrapper])

	useEffect(() => {
		if (direction !== "vertical") return
		if (!defaultHighlighted) {
			reset()
			return
		}
		if (!listRef.current) return
		const listHeight = listRef.current.getBoundingClientRect().height
		const delay = listHeight <= 340 ? 0 : 100

		return highlightFirstItem(delay)
	}, [defaultHighlighted, direction, highlightFirstItem, reset])

	/* End Vertical List Methods */

	const resetOnViewportLeave = useCallback(() => {
		reset(true)
	}, [reset])

	useMouseLeaveListener(resetOnViewportLeave)

	return (
		<div
			ref={wrapperRef}
			className={`relative ${direction !== "vertical" && "tabs-wrapper"}`}
			onMouseLeave={() => {
				reset(true)
			}}>
			<div ref={highlighterRef} className="tabs-highlighter z-0" />
			<ul
				data-cy="tabs-list"
				className={`list-none items-center ${
					direction === "vertical"
						? "grid grid-flow-row"
						: "flex flex-row gap-x-2"
				}`}
				ref={listRef}>
				{items.map((item, index) => {
					const { className, bgColor, bgDark, color, onClick } = item
					return (
						<li
							key={item.label}
							aria-label="tab"
							className={`${direction !== "vertical" && "whitespace-nowrap"} ${
								color ||
								"text-gray-500 dark:text-gray-400 dark:transition-colors dark:hover:text-gray-300"
							} ${className || ""} z-10 cursor-pointer rounded-md`}
							onMouseOver={(e) => {
								if (item.hoverable !== false) {
									highlight(e, bgColor, bgDark, "", index)
								} else if (direction !== "vertical") {
									// horizontal tabs, terminate highlighting on unhoverable items
									reset()
								}
							}}
							onClick={onClick}>
							{item.component || (
								<TabItemComponent {...item} key={item.label} index={index} />
							)}
						</li>
					)
				})}
			</ul>
		</div>
	)
}

export default Tabs
