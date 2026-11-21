import { Icon } from "@twilight-toolkit/ui"
import { useTheme } from "next-themes"
import { useRouter } from "next/router"
import type React from "react"
import { useEffect, useRef } from "react"
import { deactivateKbar, updateKbarView, useAppState } from "~/store"

import HotkeyHelper from "../Helpers/hotKey"
import Tabs, { type TabItemProps } from "../Tabs"
import { useKbarContext } from "./context"

const ListComponentLoading = ({
	resolvedTheme,
}: {
	resolvedTheme: string | undefined
}) => {
	if (resolvedTheme === "dark") {
		return <div data-cy="kbar-list-loading" className="h-full" />
	}

	return (
		<div
			data-cy="kbar-list-loading"
			className="h-[44px] w-full animate-pulse rounded-md bg-gray-100"
		/>
	)
}

const ListComponent = ({
	tabsListItems,
	verticalListWrapper,
	setWrapperHeight,
}: {
	tabsListItems: TabItemProps[]
	verticalListWrapper: React.RefObject<HTMLDivElement | null>
	setWrapperHeight: (height: number) => void
}) => {
	const { resolvedTheme } = useTheme()
	const loading = useAppState((s) => s.kbar.loading)

	// The loading and empty states have fixed heights the list itself cannot measure.
	useEffect(() => {
		let wrapperHeight = 0

		if (loading) {
			wrapperHeight = resolvedTheme === "dark" ? 360 : 66.39
		} else if (tabsListItems.length === 0) {
			wrapperHeight = 66.39
		}

		if (wrapperHeight) {
			setWrapperHeight(wrapperHeight)
		}
	}, [loading, resolvedTheme, setWrapperHeight, tabsListItems])

	if (loading) {
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
			onListHeightChange={setWrapperHeight}
		/>
	)
}

const KbarPanel = () => {
	const router = useRouter()
	const { resolvedTheme } = useTheme()
	const {
		inputValue,
		setInputValue,
		inputValueChangeHandler,
		setInputValueChangeHandler,
	} = useKbarContext()
	const { list, placeholder, animation, location, loading } = useAppState(
		(s) => s.kbar
	)
	const verticalListWrapper = useRef<HTMLDivElement>(null)

	const setWrapperHeight = (height: number) => {
		if (verticalListWrapper.current) {
			verticalListWrapper.current.style.height = `${height}px`
		}
	}

	const initialListItems: TabItemProps[] = list.map((item) => {
		let actionFunc = item.action
		const externalLink = item.link?.external
		const internalLink = item.link?.internal
		const sublist = item.sublist

		if (externalLink) {
			actionFunc = () => {
				window.open(externalLink, "_blank", "noopener,noreferrer")
			}
		} else if (internalLink) {
			actionFunc = () => {
				void router.push(internalLink)
			}
		}

		if (sublist) {
			actionFunc = () => {
				const nextView: Parameters<typeof updateKbarView>[0] = {
					key: sublist.key,
					location: [...location, sublist.key],
					items: sublist.list,
				}
				if (sublist.placeholder) {
					nextView.placeholder = sublist.placeholder
				}
				updateKbarView(nextView)
			}
		}

		const action = actionFunc
		const onClick = action
			? () => {
					if (item.singleton !== false && !item.sublist) {
						deactivateKbar()
					}

					if (item.link?.internal) {
						setTimeout(() => {
							action()
						}, 250)
					} else {
						action()
					}

					setInputValue("")

					if (item.onInputChange) {
						setInputValueChangeHandler(() => item.onInputChange)
					} else {
						setInputValueChangeHandler(undefined)
					}
				}
			: undefined

		const tabItem: TabItemProps = {
			label: item.label,
			className: "w-full justify-start! p-4!",
			component:
				item.hoverable === false ? (
					<p className="kbar-list-heading text-sm text-gray-400">
						{item.label}
					</p>
				) : (
					<div className="flex w-full items-center justify-between">
						<div
							className={`flex w-4/5 items-center gap-x-3 ${item.color ?? ""}`}>
							{item.icon && (
								<span className="flex h-5 w-5 items-center">
									<Icon name={item.icon} />
								</span>
							)}
							<span>{item.label}</span>
						</div>
						<div className="flex items-center gap-x-2.5">
							{item.description && (
								<div className="text-sm text-gray-400">{item.description}</div>
							)}
							{Boolean(item.shortcut?.length) && (
								<ul className="flex list-none gap-x-2 text-gray-500">
									<li className="rounded-md border bg-gray-100 px-2 py-0.5 text-xs capitalize dark:border-gray-600 dark:bg-transparent">
										Shift
									</li>
									{item.shortcut?.map((shortcut) => (
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

		if (item.icon) tabItem.icon = item.icon
		if (item.color) tabItem.color = item.color
		if (item.bgColor) tabItem.bgColor = item.bgColor
		if (item.bgDark) tabItem.bgDark = item.bgDark
		if (item.link) tabItem.link = item.link
		if (onClick) tabItem.onClick = onClick
		if (item.hoverable !== undefined) tabItem.hoverable = item.hoverable
		if (item.shortcut) tabItem.shortcut = item.shortcut
		if (item.description) tabItem.description = item.description

		return tabItem
	})

	const tabsListItems =
		initialListItems.length === 0 || Boolean(inputValueChangeHandler)
			? initialListItems
			: initialListItems.filter(
					(item) =>
						!inputValue ||
						(item.hoverable !== false &&
							item.label.toLowerCase().includes(inputValue.toLowerCase()))
				)

	return (
		<div
			data-cy="kbar-panel"
			onClick={(event) => {
				if (event.target === event.currentTarget) deactivateKbar()
			}}
			className="pointer-events-auto absolute -ml-10 flex h-screen w-screen justify-center">
			{initialListItems.map((item) => {
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
			})}
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
						aria-label="Search posts and commands"
						placeholder={placeholder}
						onChange={(e) => setInputValue(e.target.value)}
						value={inputValue}
						className="w-full flex-1 rounded-tl-lg rounded-tr-lg bg-transparent px-5 py-4.5 text-lg text-gray-600 outline-hidden dark:text-gray-300"
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
											newLocation.length === location.length &&
											newLocation.every((entry, idx) => entry === location[idx])
										) {
											return
										}

										updateKbarView({ key, location: newLocation })
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
						loading ? "" : "overflow-y-auto"
					} kbar-mask kbar-list max-h-[360px]`}>
					<ListComponent
						tabsListItems={tabsListItems}
						verticalListWrapper={verticalListWrapper}
						setWrapperHeight={setWrapperHeight}
					/>
				</div>
			</div>
		</div>
	)
}

export default KbarPanel
