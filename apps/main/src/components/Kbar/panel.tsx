import { Icon } from "@twilight-toolkit/ui"
import { useRouter } from "next/router"
import { useTheme } from "next-themes"
import type React from "react"
import { useContext, useEffect, useMemo, useRef } from "react"
import ContentLoader from "react-content-loader"
import { useDispatch, useSelector } from "~/hooks"
import { deactivateKbar, updateKbar } from "~/store/kbar/actions"
import { selectKbar } from "~/store/kbar/selectors"
import HotkeyHelper from "../Helpers/hotKey"
import Tabs, { type TabItemProps } from "../Tabs"
import { kbarContext } from "./context"

const ListComponentLoading = ({ resolvedTheme }: { resolvedTheme: string }) => {
	if (resolvedTheme === "dark") {
		return <div data-cy="kbar-list-loading" className="h-full" />
	}

	return (
		<ContentLoader
			uniqueKey="kbar-loading-list-item"
			speed={2}
			width={100}
			style={{ width: "100%" }}
			height={44}
			backgroundColor="#f3f3f3"
			foregroundColor="#ecebeb">
			<rect x="0" y="0" rx="5" ry="5" width="100%" height="44" />
		</ContentLoader>
	)
}

// Kbar list helper component
const ListComponent = ({
	tabsListItems,
	verticalListWrapper,
}: {
	tabsListItems: TabItemProps[] | null
	verticalListWrapper: React.MutableRefObject<HTMLDivElement | null>
}) => {
	const { resolvedTheme } = useTheme()
	const { loading } = useSelector(selectKbar)

	// update vertical list wrapper height
	// when the list is loading or no results found
	useEffect(() => {
		if (!verticalListWrapper.current) return
		let wrapperHeight = 0

		if (loading) {
			wrapperHeight = resolvedTheme === "dark" ? 360 : 66.39
		} else if (!tabsListItems) {
			wrapperHeight = 360
		} else if (tabsListItems.length === 0) {
			wrapperHeight = 66.39
		}

		if (wrapperHeight) {
			verticalListWrapper.current.style.height = `${wrapperHeight}px`
		}
	}, [loading, resolvedTheme, verticalListWrapper, tabsListItems])

	if (loading || tabsListItems == null) {
		return <ListComponentLoading resolvedTheme={resolvedTheme} />
	}

	if (tabsListItems.length === 0) {
		return (
			<div className="flex gap-x-3 p-4 text-gray-500 dark:text-gray-400">
				<span className="h-5 w-5">
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

const KbarPanel = () => {
	const router = useRouter()
	const dispatch = useDispatch()
	const { resolvedTheme } = useTheme()
	const {
		inputValue,
		setInputValue,
		inputValueChangeHandler,
		setInputValueChangeHandler,
	} = useContext(kbarContext)
	const { list, placeholder, animation, location, loading } =
		useSelector(selectKbar)
	const verticalListWrapper = useRef<HTMLDivElement>(null)

	const initialListItems = useMemo(() => {
		if (!list) return null

		return list.map((item) => {
			let actionFunc = item.action

			if (item.link) {
				if (item.link.external) {
					actionFunc = () => {
						window
							.open(item.link.external, "_blank", "noopener,noreferrer")
							?.focus()
					}
				} else if (item.link.internal) {
					actionFunc = () => {
						router.push(item.link.internal)
					}
				}
			}

			if (item.sublist) {
				actionFunc = () => {
					dispatch(
						updateKbar({
							key: item.sublist.key,
							location: [...location, item.sublist.key],
							items: item.sublist.list,
							placeholder: item.sublist.placeholder,
						})
					)
				}
			}

			const onClick = actionFunc
				? () => {
						if (item.singleton !== false && !item.sublist) {
							dispatch(deactivateKbar())
						}

						if (item.link) {
							setTimeout(() => {
								actionFunc()
							}, 250)
						} else {
							actionFunc()
						}

						setInputValue("")

						if (item.onInputChange) {
							setInputValueChangeHandler(() => item.onInputChange)
						} else {
							setInputValueChangeHandler(undefined)
						}
					}
				: undefined

			return {
				label: item.label,
				icon: item.icon,
				color: item.color,
				bgColor: item.bgColor,
				bgDark: item.bgDark,
				link: item.link,
				onClick,
				hoverable: item.hoverable,
				shortcut: item.shortcut,
				description: item.description,
				className: "w-full justify-start! p-4!",
				component:
					item.hoverable === false ? (
						<p className="kbar-list-heading text-gray-400 text-sm">
							{item.label}
						</p>
					) : (
						<div className="flex w-full items-center justify-between">
							<div
								className={`flex w-4/5 items-center gap-x-3 ${
									item.color || ""
								}`}>
								{item.icon && (
									<span className="flex h-5 w-5 items-center">
										<Icon name={item.icon} />
									</span>
								)}
								<span>{item.label}</span>
							</div>
							<div className="flex items-center gap-x-2.5">
								{item.description && (
									<div className="text-gray-400 text-sm">
										{item.description}
									</div>
								)}
								{item.shortcut?.length && (
									<ul className="flex list-none gap-x-2 text-gray-500">
										<li className="rounded-md border bg-gray-100 px-2 py-0.5 text-xs capitalize dark:border-gray-600 dark:bg-transparent">
											Shift
										</li>
										{item.shortcut.map((shortcut) => (
											<li
												key={shortcut}
												className="rounded-md border bg-gray-100 px-2 py-0.5 text-xs capitalize dark:border-gray-600 dark:bg-transparent">
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
	}, [
		dispatch,
		list,
		location,
		router,
		setInputValue,
		setInputValueChangeHandler,
	])

	const tabsListItems = useMemo(() => {
		if (
			!initialListItems ||
			!initialListItems.length ||
			!!inputValueChangeHandler
		) {
			return initialListItems
		}

		return initialListItems.filter((item) => {
			return (
				!inputValue ||
				(item.hoverable !== false &&
					item.label.toLowerCase().includes(inputValue.toLowerCase()))
			)
		})
	}, [inputValue, inputValueChangeHandler, initialListItems])

	return (
		<div
			data-cy="kbar-panel"
			className="-ml-10 pointer-events-auto absolute flex h-screen w-screen justify-center">
			{
				// register shortcuts of list items
				initialListItems?.map((item) => {
					if (item.shortcut?.length && item.onClick) {
						return (
							<HotkeyHelper
								key={item.label}
								onTrigger={item.onClick}
								shortcut={item.shortcut}
							/>
						)
					}

					return null
				})
			}
			<div
				className={`z-50 mt-[8%] ml-15 h-fit max-h-[420px] w-[620px] overflow-hidden rounded-xl border bg-white/70 shadow-2xl backdrop-blur-lg dark:border-gray-700 dark:bg-black/70 ${
					animation === "transition"
						? "animate-kbar-transition"
						: animation === "out"
							? "animate-kbar-out"
							: animation === "in"
								? "animate-kbar opacity-0"
								: ""
				}`}>
				<div
					className={`h-[60px] border-b ${
						loading ? "dark:border-gray-800" : "dark:border-gray-700"
					} flex`}>
					<input
						data-cy="kbar-input"
						placeholder={placeholder}
						onChange={(e) => setInputValue(e.target.value)}
						value={inputValue}
						className="w-full flex-1 rounded-tl-lg rounded-tr-lg bg-transparent px-5 py-4.5 text-gray-600 text-lg outline-hidden dark:text-gray-300"
						autoFocus
					/>
					<div className="mr-5 flex items-center">
						<ul className="flex list-none gap-x-2 text-gray-400 dark:text-gray-500">
							{location.map((key) => (
								<li
									key={key}
									onClick={() => {
										const newLocation = location.slice(
											0,
											location.indexOf(key) + 1
										)

										if (
											JSON.stringify(newLocation) === JSON.stringify(location)
										) {
											return
										}

										dispatch(
											updateKbar({
												key,
												location: newLocation,
											})
										)
										setInputValue("")
										setInputValueChangeHandler(undefined)
									}}
									className="cursor-pointer rounded-md border px-2 py-1 text-xs capitalize hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-800">
									{key}
								</li>
							))}
						</ul>
					</div>
					{loading && resolvedTheme === "dark" && (
						<div className="kbar-loading-bar absolute bottom-[-1.25px] z-50 h-[1.5px] w-full animate-kbar-loading-bar" />
					)}
				</div>
				<div
					data-cy="kbar-list"
					ref={verticalListWrapper}
					className={`overflow-hidden px-2.5 py-2.5 ${
						!loading && "overflow-y-auto"
					} kbar-mask kbar-list max-h-[360px]`}>
					<ListComponent
						tabsListItems={tabsListItems}
						verticalListWrapper={verticalListWrapper}
					/>
				</div>
			</div>
		</div>
	)
}

export default KbarPanel
