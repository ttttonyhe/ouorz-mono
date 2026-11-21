import type React from "react"
import { useEffect, useEffectEvent, useRef } from "react"
import { useHotkeys } from "react-hotkeys-hook"
import { useMouseLeaveListener } from "~/hooks"
import scrollToItemWithinDiv from "~/utilities/scrollTo"

import type { TabsProps } from "."
import TabItemComponent from "./item"

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

	const styleHighlighter = (
		visible: boolean,
		wrapperBoundingBox?: DOMRect | null,
		tabBoundingBox?: DOMRect | null,
		bgColor?: string,
		bgDark?: string,
		className?: string
	) => {
		const highlighter = highlighterRef.current
		if (!highlighter) return

		highlighter.style.transitionDuration =
			visible && (withinWrapperRef.current || highlightedIndexRef.current > 0)
				? "150ms"
				: "0ms"

		highlighter.style.opacity = visible ? "1" : "0"

		if (visible && tabBoundingBox) {
			highlighter.style.width =
				highlightedIndexRef.current === -1 &&
				defaultHighlighted &&
				direction === "vertical"
					? "100%"
					: `${tabBoundingBox.width}px`
			highlighter.style.height =
				highlightedIndexRef.current === -1 &&
				defaultHighlighted &&
				direction === "vertical"
					? "46.3889px"
					: `${tabBoundingBox.height}px`
		} else {
			highlighter.style.width = "0"
			highlighter.style.height = "0"
		}

		if (visible && wrapperBoundingBox && tabBoundingBox) {
			highlighter.style.transform =
				direction === "vertical"
					? `translateY(${tabBoundingBox.top - wrapperBoundingBox.top}px)`
					: `translateX(${tabBoundingBox.left - wrapperBoundingBox.left}px)`
		} else {
			highlighter.style.transform = "none"
		}

		highlighter.className = visible
			? [
					"tabs-highlighter z-0",
					className ?? "",
					bgColor ?? "bg-menu",
					bgDark ?? "dark:bg-gray-700/40 backdrop-blur-xs",
				].join(" ")
			: ""
	}

	const reset = (skipVertical?: boolean) => {
		if (skipVertical && direction === "vertical") return
		withinWrapperRef.current = false
		styleHighlighter(false)
		highlightedIndexRef.current = -1
	}

	const highlight = (
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
			const targetItem = items[targetIndex]
			if (!element || !targetItem) return

			target = element
			targetBgColor = targetItem.bgColor
			targetBgDark = targetItem.bgDark
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
		withinWrapperRef.current = true
		if (targetIndex >= 0) highlightedIndexRef.current = targetIndex
	}

	const highlightFirstItem = useEffectEvent((delay: number) => {
		const timeout = window.setTimeout(() => {
			const firstChild = listRef.current?.children[0]
			const firstItem = items[0]
			if (!firstChild) return
			if (firstItem && direction === "vertical" && defaultHighlighted) {
				highlight(
					firstChild,
					firstItem.bgColor,
					firstItem.bgDark,
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
	})

	useHotkeys(
		"down",
		(e) => {
			if (direction !== "vertical") return
			e.preventDefault()
			const current = highlightedIndexRef.current
			const targetIndex = current + 1 < items.length ? current + 1 : 0
			const targetElement = listRef.current?.children[targetIndex]
			const targetItem = items[targetIndex]
			if (!targetElement || !targetItem) return

			highlight(
				targetElement,
				targetItem.bgColor,
				targetItem.bgDark,
				"",
				targetIndex,
				"above"
			)
		},
		{ enableOnFormTags: ["INPUT"] },
		[direction, items]
	)

	useHotkeys(
		"up",
		(e) => {
			if (direction !== "vertical") return
			e.preventDefault()
			const current = highlightedIndexRef.current
			const targetIndex = current - 1 >= 0 ? current - 1 : items.length - 1
			const targetElement = listRef.current?.children[targetIndex]
			const targetItem = items[targetIndex]
			if (!targetElement || !targetItem) return

			highlight(
				targetElement,
				targetItem.bgColor,
				targetItem.bgDark,
				"",
				targetIndex,
				"below"
			)
		},
		{ enableOnFormTags: ["INPUT"] },
		[direction, items]
	)

	useHotkeys(
		"enter",
		(e) => {
			if (direction !== "vertical") return
			e.preventDefault()
			items[highlightedIndexRef.current]?.onClick?.()
		},
		{ enableOnFormTags: ["INPUT"] },
		[direction, items]
	)

	const reportHeight = useEffectEvent((height: number) => {
		onListHeightChange?.(height)
	})

	useEffect(() => {
		if (direction !== "vertical" || items.length === 0 || !listRef.current) {
			return
		}
		const listHeight = listRef.current.getBoundingClientRect().height
		reportHeight(listHeight >= 340 ? 360 : listHeight + 20)
	}, [direction, items])

	const resetOnEffect = useEffectEvent(() => reset())

	useEffect(() => {
		if (direction !== "vertical") return
		if (!defaultHighlighted || items.length === 0) {
			resetOnEffect()
			return
		}
		if (!listRef.current) return
		const listHeight = listRef.current.getBoundingClientRect().height
		const delay = listHeight <= 340 ? 0 : 100

		return highlightFirstItem(delay)
	}, [defaultHighlighted, direction, items])

	useMouseLeaveListener(() => reset(true))

	return (
		<div
			ref={wrapperRef}
			className={`relative ${direction === "vertical" ? "" : "tabs-wrapper"}`}
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
							role={item.component && onClick ? "button" : undefined}
							tabIndex={item.component && onClick ? 0 : undefined}
							onKeyDown={(event) => {
								if (
									event.target === event.currentTarget &&
									(event.key === "Enter" || event.key === " ") &&
									onClick
								) {
									event.preventDefault()
									event.stopPropagation()
									onClick()
								}
							}}
							className={`${direction === "vertical" ? "" : "whitespace-nowrap"} ${
								color ??
								"text-gray-500 dark:text-gray-400 dark:transition-colors dark:hover:text-gray-300"
							} ${className ?? ""} z-10 cursor-pointer rounded-md`}
							onFocus={(e) => {
								if (item.hoverable !== false) {
									highlight(e.currentTarget, bgColor, bgDark, "", index)
								}
							}}
							onMouseOver={(e) => {
								if (item.hoverable !== false) {
									highlight(e, bgColor, bgDark, "", index)
								} else if (direction !== "vertical") {
									reset()
								}
							}}
							onClick={onClick}>
							{item.component ?? (
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
