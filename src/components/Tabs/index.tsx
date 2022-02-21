import Link from 'next/link'
import React, { useRef, useState } from 'react'
import Icons from '~/components/Icons'

interface TabItemProps {
	label: string
	color?: string
	bgColor?: string
	bgDark?: string
	icon?: string
	link?: {
		internal?: string
		external?: string
	}
	onClick?: () => void
}

interface Props {
	items: TabItemProps[]
}

type HighlightFunc = (
	e: React.MouseEvent<HTMLElement>,
	bgColor?: string,
	bgDark?: string
) => void

interface TabItemComponentProps extends TabItemProps {
	highlight: HighlightFunc
}

const TabItemComponent = (props: TabItemComponentProps) => {
	const { label, icon, color, bgColor, bgDark, link, onClick, highlight } =
		props

	const TabButton = () => (
		<button
			aria-label="tab"
			className={`w-max py-2 px-5 rounded-md cursor-pointer focus:outline-none justify-center items-center text-xl tracking-wider flex text-gray-500 dark:text-gray-400 lg:flex z-10 ${
				color || ''
			}`}
			onClick={onClick}
			onMouseEnter={(e) => highlight(e, bgColor, bgDark)}
		>
			{icon && <span className="w-6 h-6 mr-1">{Icons[icon]}</span>}
			{label}
		</button>
	)

	if (link?.internal) {
		return (
			<Link href={link.internal}>
				<a className="z-10">
					<TabButton />
				</a>
			</Link>
		)
	}

	if (link?.external) {
		return (
			<a
				className="z-10"
				href={link.external}
				rel="noopener noreferrer"
				target="_blank"
			>
				<TabButton />
			</a>
		)
	}

	return <TabButton />
}

const Tabs = (props: Props) => {
	const { items } = props
	const wrapperRef = useRef<HTMLDivElement>(null)
	const highlighterRef = useRef<HTMLDivElement>(null)
	const [withinWrapper, setWithinWrapper] = useState(false)

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
			visible && withinWrapper ? '150ms' : '0ms'

		// visibility
		highlighterRef.current.style.opacity = visible ? '1' : '0'

		// height and width
		highlighterRef.current.style.width = visible
			? `${tabBoundingBox.width}px`
			: '0'
		highlighterRef.current.style.height = visible
			? `${tabBoundingBox.height}px`
			: '0'

		// position
		highlighterRef.current.style.transform = visible
			? `translate(${tabBoundingBox.left - wrapperBoundingBox.left}px)`
			: 'none'

		// background color
		if (visible) {
			highlighterRef.current.className = `tabs-highlighter z-0 ${
				bgColor || 'bg-menu'
			} ${bgDark || 'dark:bg-gray-800'}`
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
	 */
	const highlight = (
		e: React.MouseEvent<HTMLElement>,
		bgColor?: string,
		bgDark?: string
	) => {
		const targetTabBoundingBox = e.currentTarget.getBoundingClientRect()
		const wrapperBoundingBox = wrapperRef.current?.getBoundingClientRect()
		styleHighlighter(
			true,
			wrapperBoundingBox,
			targetTabBoundingBox,
			bgColor,
			bgDark
		)
		setWithinWrapper(true)
	}

	/**
	 * Remove the highlighter
	 */
	const reset = () => {
		setWithinWrapper(false)
		styleHighlighter(false)
	}

	return (
		<div ref={wrapperRef} className="relative" onMouseLeave={reset}>
			<div ref={highlighterRef} className="tabs-highlighter z-0" />
			<div className="flex items-center gap-x-2">
				{items.map((item) => {
					return (
						<TabItemComponent
							{...item}
							key={item.label}
							highlight={highlight}
						/>
					)
				})}
			</div>
		</div>
	)
}

export default Tabs
