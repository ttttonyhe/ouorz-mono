import React, { useEffect, useRef, useState } from 'react'
import { useHotkeys } from 'react-hotkeys-hook'
import scrollToItemWithinDiv from '~/utilities/scrollTo'
import { TabsProps } from '.'
import TabItemComponent from './item'

const Tabs = (props: TabsProps) => {
	const { items, direction, defaultHighlighted, verticalListWrapper } = props
	const wrapperRef = useRef<HTMLDivElement>(null)
	const highlighterRef = useRef<HTMLDivElement>(null)
	const listRef = useRef<HTMLUListElement>(null)
	const [withinWrapper, setWithinWrapper] = useState(false)
	const [highlightedIndex, setHighlightedIndex] = useState<number>(-1)

	/**
	 * Change the styling of the highlighter
	 *
	 * @param {boolean} visible
	 * @param {(DOMRect | null)} [wrapperBoundingBox]
	 * @param {(DOMRect | null)} [tabBoundingBox]
	 * @param {string} [bgColor]
	 * @param {string} [bgDark]
	 */
	const styleHighlighter = (
		visible: boolean,
		wrapperBoundingBox?: DOMRect | null,
		tabBoundingBox?: DOMRect | null,
		bgColor?: string,
		bgDark?: string
	) => {
		if (!highlighterRef.current) return

		// animation duration
		highlighterRef.current.style.transitionDuration =
			visible && (withinWrapper || highlightedIndex > 0) ? '150ms' : '0ms'

		// visibility
		highlighterRef.current.style.opacity = visible ? '1' : '0'

		// width
		if (visible) {
			highlighterRef.current.style.width =
				highlightedIndex === -1 &&
				defaultHighlighted &&
				direction === 'vertical'
					? '100%'
					: `${tabBoundingBox.width}px`
		} else {
			highlighterRef.current.style.width = '0'
		}

		// height
		if (visible) {
			highlighterRef.current.style.height =
				highlightedIndex === -1 &&
				defaultHighlighted &&
				direction === 'vertical'
					? '46.3889px'
					: `${tabBoundingBox.height}px`
		} else {
			highlighterRef.current.style.height = '0'
		}

		// position
		if (visible) {
			highlighterRef.current.style.transform =
				direction === 'vertical'
					? `translateY(${tabBoundingBox.top - wrapperBoundingBox.top}px)`
					: `translateX(${tabBoundingBox.left - wrapperBoundingBox.left}px)`
		} else {
			highlighterRef.current.style.transform = 'none'
		}

		// background color
		if (visible) {
			highlighterRef.current.className = `tabs-highlighter z-0 ${
				bgColor || 'bg-menu'
			} ${bgDark || 'dark:bg-gray-700/40 backdrop-blur-sm'}`
		} else {
			highlighterRef.current.className = ''
		}
	}

	/**
	 * Highlight the tab that the mouse is currently hovering over
	 *
	 * @param {React.MouseEvent<HTMLElement>} e
	 * @param {string} [bgColor]
	 * @param {string} [bgDark]
	 * @param {number} [index] - index of the tab to highlight
	 * @param {string} [from] - highlighter initial direction
	 */
	const highlight = (
		e: React.MouseEvent<HTMLElement> | Element,
		bgColor?: string,
		bgDark?: string,
		index?: number,
		from?: 'above' | 'below'
	) => {
		// skip unhoverable tabs
		if (items[index]?.hoverable === false && e instanceof Element) {
			const targetIndex =
				from === 'below'
					? index - 1 >= 0
						? index - 1
						: items.length - 1
					: index + 1 < items.length
					? index + 1
					: 0
			if (targetIndex >= 0 && targetIndex < items.length) {
				highlight(
					listRef.current.children[targetIndex],
					items[targetIndex].bgColor,
					items[targetIndex].bgDark,
					targetIndex,
					from
				)
			}
			return
		}

		// vertical tabs have a different scroll behavior
		verticalListWrapper &&
			scrollToItemWithinDiv(
				verticalListWrapper.current,
				listRef.current.children[index]
			)

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
			bgDark
		)
		setWithinWrapper(true)
		index >= 0 && setHighlightedIndex(index)
	}

	/**
	 * Remove the highlighter
	 */
	const reset = () => {
		setWithinWrapper(false)
		styleHighlighter(false)
		setHighlightedIndex(-1)
	}

	// vertical tabs, register keyboard shortcuts
	if (direction === 'vertical') {
		// navigation
		useHotkeys(
			'down',
			(e) => {
				e.preventDefault()
				const targetIndex =
					highlightedIndex + 1 < items.length ? highlightedIndex + 1 : 0
				highlight(
					listRef.current.children[targetIndex],
					items[targetIndex].bgColor,
					items[targetIndex].bgDark,
					targetIndex,
					'above'
				)
			},
			{
				enableOnTags: ['INPUT'],
			},
			[highlightedIndex]
		)
		useHotkeys(
			'up',
			(e) => {
				e.preventDefault()
				const targetIndex =
					highlightedIndex - 1 >= 0 ? highlightedIndex - 1 : items.length - 1
				highlight(
					listRef.current.children[targetIndex],
					items[targetIndex].bgColor,
					items[targetIndex].bgDark,
					targetIndex,
					'below'
				)
			},
			{
				enableOnTags: ['INPUT'],
			},
			[highlightedIndex]
		)
		// action triggerer
		useHotkeys(
			'enter',
			(e) => {
				e.preventDefault()
				items[highlightedIndex].onClick !== null &&
					items[highlightedIndex].onClick()
			},
			{
				enableOnTags: ['INPUT'],
			},
			[highlightedIndex]
		)
	}

	// highlight the first tab by default if direction is vertical and
	// defaultHighlighted is true
	useEffect(() => {
		if (!listRef.current) return
		if (items[0] && direction === 'vertical' && defaultHighlighted) {
			highlight(
				listRef.current.children[0],
				items[0].bgColor,
				items[0].bgDark,
				0,
				'above'
			)
		} else {
			reset()
		}
	}, [items, direction, defaultHighlighted, listRef])

	return (
		<div
			ref={wrapperRef}
			className="relative"
			onMouseLeave={() => {
				direction !== 'vertical' && reset()
			}}
		>
			<div ref={highlighterRef} className="tabs-highlighter z-0" />
			<ul
				className={`items-center list-none ${
					direction === 'vertical'
						? 'grid grid-flow-row'
						: 'flex flex-row gap-x-2'
				}`}
				ref={listRef}
			>
				{items.map((item, index) => {
					const { className, bgColor, bgDark, color, onClick } = item
					return (
						<li
							key={item.label}
							aria-label="tab"
							className={`z-10 ${
								color ||
								'text-gray-500 dark:text-gray-400 dark:hover:text-gray-300 dark:transition-colors'
							} ${className || ''} cursor-pointer rounded-md`}
							onMouseEnter={(e) => {
								item.hoverable !== false && highlight(e, bgColor, bgDark, index)
								// horizontal tabs, terminate highlighting on unhoverable items
								item.hoverable === false && direction !== 'vertical' && reset()
							}}
							onClick={onClick}
						>
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
