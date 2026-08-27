import type React from "react"
import { useCallback, useEffect, useRef } from "react"
import { useHotkeys } from "react-hotkeys-hook"

import { useMouseLeaveListener } from "~/hooks"
import scrollToItemWithinDiv from "~/utilities/scrollTo"

import type { TabsProps } from "."
import TabItemComponent from "./item"

/** Walks past unhoverable tabs in the given direction, wrapping at both ends. */
const nextHoverableIndex = (
	items: TabsProps["items"],
	index: number,
	from?: "above" | "below"
): number => {
	let cursor = index
	for (let step = 0; step < items.length; step += 1) {
		if (items[cursor]?.hoverable !== false) return cursor
		cursor =
			from === "below"
				? cursor - 1 >= 0
					? cursor - 1
					: items.length - 1
				: cursor + 1 < items.length
					? cursor + 1
					: 0
	}
	return -1
}

const Tabs = (props: TabsProps) => {
	const {
		items,
		direction,
		defaultHighlighted,
		verticalListWrapper,
		onListHeightChange,
	} = props
	const wrapperRef = useRef<HTMLDivElement>(null)
	const highlighterRef = useRef<HTMLDivElement>(null)
	const listRef = useRef<HTMLUListElement>(null)
	const withinWrapperRef = useRef(false)
	const highlightedIndexRef = useRef(-1)

	const updateWithinWrapper = useCallback((value: boolean) => {
		withinWrapperRef.current = value
	}, [])

	const updateHighlightedIndex = useCallback((value: number) => {
		highlightedIndexRef.current = value
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
			let target: React.MouseEvent<HTMLElement> | Element = e
			let targetIndex = index
			let targetBgColor = bgColor
			let targetBgDark = bgDark

			if (items[index]?.hoverable === false && e instanceof Element) {
				targetIndex = nextHoverableIndex(items, index, from)
				const element =
					targetIndex >= 0 ? listRef.current?.children[targetIndex] : undefined
				if (!element) return

				target = element
				targetBgColor = items[targetIndex].bgColor
				targetBgDark = items[targetIndex].bgDark
			}

			const targetListElement = listRef.current?.children[targetIndex]

			if (verticalListWrapper?.current && targetListElement) {
				scrollToItemWithinDiv(verticalListWrapper.current, targetListElement)
			}

			const targetTabBoundingBox =
				target instanceof Element
					? target.getBoundingClientRect()
					: target.currentTarget.getBoundingClientRect()
			const wrapperBoundingBox = wrapperRef.current?.getBoundingClientRect()
			styleHighlighter(
				true,
				wrapperBoundingBox,
				targetTabBoundingBox,
				targetBgColor,
				targetBgDark,
				className
			)
			updateWithinWrapper(true)
			if (targetIndex >= 0) updateHighlightedIndex(targetIndex)
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
			const current = highlightedIndexRef.current
			const targetIndex = current + 1 < items.length ? current + 1 : 0
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
		[direction, highlight, items]
	)
	useHotkeys(
		"up",
		(e) => {
			if (direction !== "vertical") return
			e.preventDefault()
			const current = highlightedIndexRef.current
			const targetIndex = current - 1 >= 0 ? current - 1 : items.length - 1
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
		[direction, highlight, items]
	)
	useHotkeys(
		"enter",
		(e) => {
			if (direction !== "vertical") return
			e.preventDefault()
			items[highlightedIndexRef.current]?.onClick?.()
		},
		{
			enableOnFormTags: ["INPUT"],
		},
		[direction, items]
	)

	// Latest-callback ref: reporting the height is event-like, not a dependency of
	// the measurement itself.
	const onListHeightChangeRef = useRef(onListHeightChange)
	useEffect(() => {
		onListHeightChangeRef.current = onListHeightChange
	})

	useEffect(() => {
		if (direction !== "vertical" || items.length === 0 || !listRef.current) {
			return
		}
		const listHeight = listRef.current.getBoundingClientRect().height
		onListHeightChangeRef.current?.(listHeight >= 340 ? 360 : listHeight + 20)
	}, [direction, items])

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
							onFocus={(e) => {
								if (item.hoverable !== false) {
									highlight(e.currentTarget, bgColor, bgDark, "", index)
								}
							}}
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
