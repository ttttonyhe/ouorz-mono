import { KbarContextProvider, kbarContext } from './context'
import { KbarProps } from '.'
import React, { useContext, useEffect, useState, useRef } from 'react'
import { useTheme } from 'next-themes'
import { useRouter } from 'next/router'
import dynamic from 'next/dynamic'
import { useHotkeys } from 'react-hotkeys-hook'
import Tabs, { TabItemProps } from '../Tabs'
import Icons from '../Icons'

const ContentLoader = dynamic(() => import('react-content-loader'))

const KbarComponent = ({ beforeHide }: { beforeHide: boolean }) => {
	const { theme } = useTheme()
	const { list, loading, inputValue, placeholder, setInputValue } =
		useContext(kbarContext)
	const router = useRouter()
	const verticalListWrapper = useRef<HTMLDivElement>(null)

	// preprocess items list
	list.map((item) => {
		// create action functions for link items
		let actionFunc = item.action

		if (item.link?.external) {
			actionFunc = () => {
				window.open(item.link.external, '_blank').focus()
			}
		} else if (item.link?.internal) {
			actionFunc = () => {
				router.push(item.link.internal)
			}
		}

		item.action = actionFunc

		// register shortcuts of list items
		item.shortcut?.length &&
			useHotkeys(
				`shift+${item.shortcut.join('+')}`,
				(e) => {
					e.preventDefault()
					item.action()
				},
				{
					enableOnTags: ['INPUT'],
				}
			)
	})

	// construct items data for Tabs component
	const initialTabsListItems = list.map((item) => {
		return {
			label: item.label,
			icon: item.icon,
			color: item.color,
			bgColor: item.bgColor,
			bgDark: item.bgDark,
			link: item.link,
			onClick: item.action,
			hoverable: item.hoverable,
			className: 'w-full !justify-start !p-4',
			component:
				item.hoverable === false ? (
					<p className="text-sm text-gray-400 -my-2">{item.label}</p>
				) : (
					<div className="flex justify-between w-full items-center">
						<div className={`flex gap-x-3 items-center ${item.color || ''}`}>
							{item.icon && (
								<span className="h-5 w-5 -mt-[1px]">{Icons[item.icon]}</span>
							)}
							<span>{item.label}</span>
						</div>
						<div className="flex gap-x-2.5 items-center">
							{item.description && (
								<div className="text-sm text-gray-400">{item.description}</div>
							)}
							{item.shortcut?.length && (
								<ul className="flex list-none gap-x-2 text-gray-500">
									<li className="capitalize bg-gray-100 dark:bg-transparent dark:border-gray-600 rounded-md border py-0.5 text-xs px-2">
										Shift
									</li>
									{item.shortcut.map((shortcut) => (
										<li
											key={shortcut}
											className="capitalize bg-gray-100 dark:bg-transparent dark:border-gray-600 rounded-md border py-0.5 text-xs px-2"
										>
											{shortcut}
										</li>
									))}
								</ul>
							)}
						</div>
					</div>
				),
		}
	})

	const [tabsListItems, setTabsListItems] =
		useState<TabItemProps[]>(initialTabsListItems)

	// search items in list
	useEffect(() => {
		const resultList = initialTabsListItems.filter((item) => {
			// filter out unhoverable items when input value is not empty
			return (
				!inputValue ||
				(item.hoverable !== false &&
					item.label.toLowerCase().includes(inputValue.toLowerCase()))
			)
		})
		setTabsListItems(resultList)
	}, [inputValue])

	const ListComponent = () => {
		if (loading) {
			return (
				<ContentLoader
					speed={2}
					width={100}
					style={{ width: '100%' }}
					height={100}
					backgroundColor={theme === 'dark' ? '#52525b' : '#f3f3f3'}
					foregroundColor={theme === 'dark' ? '#71717a' : '#ecebeb'}
				>
					<rect x="0" y="0" rx="5" ry="5" width="100%" height="50" />
				</ContentLoader>
			)
		}

		if (tabsListItems.length === 0) {
			return (
				<div className="flex gap-x-3 text-gray-500 dark:text-gray-400 p-4">
					<span className="w-5 h-5">{Icons['empty']}</span>
					<span>No results found</span>
				</div>
			)
		}

		return (
			<Tabs
				items={tabsListItems}
				direction="vertical"
				defaultHighlighted
				verticalListWrapper={verticalListWrapper}
			/>
		)
	}

	return (
		<div className="w-screen -ml-10 h-screen flex justify-center pointer-events-auto">
			<div
				className={`z-50 w-[620px] border dark:border-gray-700 rounded-xl shadow-2xl overflow-hidden backdrop-blur-lg bg-white/70 dark:bg-black/70 mt-[8%] h-fit max-h-[420px] ${
					beforeHide ? 'animate-kbarOut' : 'animate-kbar'
				}`}
			>
				<div className="h-[60px] border-b dark:border-gray-700">
					<input
						value={inputValue}
						placeholder={placeholder}
						onChange={(e) => setInputValue(e.target.value)}
						autoFocus
						className="w-full bg-transparent rounded-tl-lg rounded-tr-lg text-lg py-4.5 px-5 outline-none text-gray-600 dark:text-gray-300"
					/>
				</div>
				<div
					ref={verticalListWrapper}
					className="px-2.5 py-2.5 overflow-hidden overflow-y-auto max-h-[360px] kbar-mask"
				>
					<ListComponent />
				</div>
			</div>
		</div>
	)
}

const Kbar = (props: KbarProps) => {
	const [display, setDisplay] = useState(false)
	const [beforeHide, setBeforeHide] = useState(false)
	const [kbarLoading, setLoading] = useState(false)
	const [kbarInputValue, setInputValue] = useState(props.inputValue)

	/**
	 * Handle the kbar display state
	 *
	 * @param {boolean} display
	 */
	const setDisplayFunc = (display: boolean) => {
		if (display) {
			setDisplay(true)
		} else {
			setBeforeHide(true)
			setTimeout(() => {
				setDisplay(false)
				setBeforeHide(false)
			}, 200)
		}
	}

	// register keybinding that triggers/hides the kbar
	useHotkeys('ctrl+k, command+k', (e) => {
		e.preventDefault()
		setDisplayFunc(true)
	})
	useHotkeys('esc', () => setDisplayFunc(false), {
		enableOnTags: ['INPUT'],
	})

	// disbale body pointer events when kbar is open
	useEffect(() => {
		const bodyDOM = document.querySelector('body')
		bodyDOM.style.pointerEvents = display ? 'none' : 'auto'

		return () => {
			bodyDOM.style.pointerEvents = 'auto'
		}
	}, [display])

	return (
		<KbarContextProvider
			value={{
				list: props.list,
				keyBinding: props.keyBinding,
				loading: kbarLoading,
				placeholder: props.placeholder || 'What are you looking for?',
				inputValue: kbarInputValue,
				setLoading,
				setInputValue,
				setDisplay: setDisplayFunc,
			}}
		>
			{display && (
				<>
					<div
						className={`absolute bg-gray-50/90 dark:bg-black/70 h-screen w-full z-40 pointer-events-auto ${
							beforeHide ? 'animate-kbarBgOut' : 'animate-kbarBg'
						}`}
						onClick={() => setDisplayFunc(false)}
					/>
					<KbarComponent beforeHide={beforeHide} />
				</>
			)}
		</KbarContextProvider>
	)
}

export default Kbar
