import { useEffect, useRef, useState } from 'react'
import Icon from '~/components/Icon'
import dynamic from 'next/dynamic'
import Tabs, { TabItemProps } from '../Tabs'
import { useTheme } from 'next-themes'
import getApi from '~/utilities/api'
import { useRouter } from 'next/router'

const ContentLoader = dynamic(() => import('react-content-loader'))

interface SearchProps {
	startSearching: boolean
	endSearching: boolean
	setStartSearching: any
	setEndSearching: any
}

export default function Search({
	startSearching,
	setStartSearching,
	endSearching,
	setEndSearching,
}: SearchProps) {
	const { resolvedTheme } = useTheme()
	const router = useRouter()
	const [searchContent, setSearchContent] = useState<string>()
	const [loading, setLoading] = useState(true)
	const [tabsListItems, setTabsListItems] = useState<TabItemProps[]>([])
	const [initialSearchableContentList, setInitialContentList] = useState<
		TabItemProps[]
	>([])
	const verticalListWrapper = useRef<HTMLDivElement>(null)

	/**
	 * Turn off search command palette
	 */
	const terminateSearch = () => {
		setEndSearching(true)
		setStartSearching(false)
		setTimeout(() => {
			setEndSearching(false)
			setSearchContent('')
			setLoading(true)
		}, 200)
		document.getElementsByTagName('body')[0].classList.remove('stop-scrolling')
	}

	// diable scrolling when search is active
	useEffect(() => {
		if (startSearching) {
			document.getElementsByTagName('body')[0].classList.add('stop-scrolling')
		} else {
			document
				.getElementsByTagName('body')[0]
				.classList.remove('stop-scrolling')
		}
	}, [startSearching])

	// fetch all searchable content
	useEffect(() => {
		fetch(
			getApi({
				searchIndexes: true,
			})
		)
			.then((res) => res.json())
			.then((res) => {
				setInitialContentList(
					res.ids.map((id: number, index: number) => {
						const title = res.titles[index]
						return {
							label: title,
							link: {
								internal: `/post/${id}`,
							},
							onClick: () => {
								terminateSearch()
								router.push(`/post/${id}`)
							},
							className: 'w-full !justify-start !p-4',
							component: (
								<div className="flex w-full justify-between items-center">
									<div className="whitespace-nowrap overflow-hidden overflow-ellipsis max-w-[420px]">
										<span>{title}</span>
									</div>
									<div className="flex gap-x-2.5 items-center">
										<div className="text-sm text-gray-400">{id}</div>
									</div>
								</div>
							),
						}
					})
				)
				setTabsListItems(initialSearchableContentList)
			})
			.finally(() => {
				setLoading(false)
			})
			.catch(() => {
				setLoading(false)
			})
	}, [])

	// search posts in list
	useEffect(() => {
		if (!loading) {
			const resultList = initialSearchableContentList.filter((item) => {
				return item.label
					.toLocaleLowerCase()
					.includes((searchContent || '').toLocaleLowerCase())
			})
			setTabsListItems(resultList)
		}
	}, [searchContent, loading, initialSearchableContentList])

	const ListComponent = () => {
		if (loading) {
			return (
				<ContentLoader
					speed={2}
					width={100}
					style={{ width: '100%' }}
					height={45}
					backgroundColor={resolvedTheme === 'dark' ? '#52525b' : '#f3f3f3'}
					foregroundColor={resolvedTheme === 'dark' ? '#71717a' : '#ecebeb'}
				>
					<rect x="0" y="0" rx="5" ry="5" width="100%" height="45" />
				</ContentLoader>
			)
		}

		if (tabsListItems.length === 0) {
			return (
				<div className="flex gap-x-3 text-gray-500 dark:text-gray-400 p-4">
					<span className="w-5 h-5">
						<Icon name="empty" />
					</span>
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

	if (!startSearching && !endSearching) return null

	return (
		<>
			<div
				className={`absolute bg-gray-50/90 dark:bg-black/70 h-screen w-full z-40 pointer-events-auto ${
					endSearching ? 'animate-kbarBgOut' : 'animate-kbarBg'
				}`}
				onClick={() => terminateSearch()}
			/>
			<div className="w-screen -ml-10 h-screen flex justify-center pointer-events-auto">
				<div
					className={`z-50 w-[620px] border dark:border-gray-700 rounded-xl shadow-2xl overflow-hidden backdrop-blur-lg bg-white/70 dark:bg-black/70 mt-[8%] h-fit max-h-[420px] ${
						endSearching ? 'animate-kbarOut' : 'animate-kbar'
					}`}
				>
					<div className="h-[60px] border-b dark:border-gray-700">
						<input
							onKeyDown={(e) => {
								if (e.key === 'Escape') {
									terminateSearch()
								}
							}}
							placeholder="Search articles..."
							value={searchContent}
							autoFocus
							onChange={(e) => {
								setSearchContent(e.target.value)
							}}
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
		</>
	)
}
