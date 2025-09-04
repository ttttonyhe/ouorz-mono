import Kbar, { KbarListItem } from "../Kbar"
import { HeaderTransition, OffsetTransition } from "../Motion"
import ScrollWrapper from "../Motion/scroll"
import Tabs from "../Tabs"
import { useTheme } from "next-themes"
import Image from "next/image"
import Link from "next/link"
import { useRouter } from "next/router"
import React, { MutableRefObject, useRef } from "react"
import { useDispatch, useSelector } from "~/hooks"
import useAnalytics from "~/hooks/analytics"
import { selectGeneral } from "~/store/general/selectors"
import {
	updateKbarToSearch,
	activateKbar,
	updateKbarSearchQuery,
} from "~/store/kbar/actions"

interface HeaderSearchBarComponentProps {
	activateKbar: () => void
}

const HeaderSearchBarComponent = ({
	activateKbar,
}: HeaderSearchBarComponentProps) => {
	return (
		<div className="effect-pressing hidden lg:flex lg:w-[65%] xl:w-[620px]">
			<div
				aria-label="Command + K to open the command palette"
				className="absolute left-3 top-[6px] z-10 cursor-not-allowed rounded-md border bg-gray-50 px-1.5 py-0.5 text-xs text-gray-400 dark:border-gray-600 dark:bg-transparent">
				⌘+K
			</div>
			<input
				type="text"
				className="outline-hidden dark:bg-gray-800/50 dark:shadow-xs w-full rounded-md border border-gray-200 bg-white/90 px-3 py-2 pl-[54px] text-sm transition-shadow hover:bg-neutral-50 dark:border-gray-700 dark:hover:border-gray-700 dark:hover:bg-gray-800"
				placeholder="Type your command or search..."
				onFocus={activateKbar}
				data-oa="click-activateKbar"
				data-cy="cmdkbutton"
			/>
		</div>
	)
}

const HeaderTitleComponent = () => {
	const { headerTitle } = useSelector(selectGeneral)

	if (!headerTitle) return null

	return (
		<div className="mx-auto hidden items-center justify-center space-x-3 overflow-hidden lg:flex">
			<h3 className="overflow-hidden text-ellipsis whitespace-nowrap font-medium">
				{headerTitle}
			</h3>
		</div>
	)
}

interface HeaderComponentProps {
	headerRef: MutableRefObject<HTMLDivElement>
}

const HeaderComponent = ({ headerRef }: HeaderComponentProps) => {
	const router = useRouter()
	const dispatch = useDispatch()
	const { trackEvent } = useAnalytics()

	const { setTheme, resolvedTheme } = useTheme()
	const titleRef = useRef<HTMLDivElement>(null)

	const nonHomePage = router.pathname.split("/").length > 2

	const leftTabItems = [
		{
			label: "Avatar",
			hoverable: false,
			component: (
				<div className="group mx-auto flex cursor-pointer items-center justify-center space-x-3 px-5">
					<div className="flex h-[18px] w-[18px] shrink-0 items-center rounded-full border border-gray-300 dark:border-gray-500">
						{/* <a
							href="https://opensea.io/assets/ethereum/0x13bd2ac3779cbbcb2ac874c33f1145dd71ce41ee/3690"
							target="_blank"
							rel="noreferrer"
							className="flex"
						> */}
						<Image
							className="rounded-full"
							src={resolvedTheme === "dark" ? "/icon-dark.png" : "/icon.png"}
							alt="Tony's NFT avatar"
							height={18}
							width={18}
							loading="lazy"
						/>
						{/* </a> */}
					</div>
					<div className="text-3 font-medium text-black">
						<Link href="/" passHref>
							<h3 className="text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300">
								Tony He
							</h3>
						</Link>
					</div>
				</div>
			),
		},
		{
			label: "Newsletter",
			className: "hidden lg:block",
			icon: "subscribe",
			link: {
				external: "https://lists.lipeng.ac/subscription/form",
			},
		},
	]

	const rightTabItems = [
		nonHomePage
			? {
					label: "Home",
					className: "hidden lg:block",
					icon: "home",
					link: {
						internal: "/",
					},
				}
			: {
					label: "AMA",
					className: "hidden lg:block",
					color: "text-pink-500",
					bgColor: "bg-pink-100",
					bgDark: "dark:bg-pink-900",
					icon: "chat",
					link: {
						internal: "/page/765",
					},
				},
		{
			label: "Pages",
			icon: "pages",
			link: {
				internal: "/pages",
			},
		},
		// {
		// 	label: "About",
		// 	icon: "me",
		// 	link: {
		// 		internal: "/post/126",
		// 	},
		// },
	]

	const kbarItems: KbarListItem[] = [
		{
			label: "Navigation",
			id: "navigation-divider",
			hoverable: false,
		},
		{
			label: "Go Back",
			id: "back",
			icon: "left",
			shortcut: ["b"],
			action: () => router.back(),
			description: "Command",
		},
		{
			label: "Home",
			id: "home",
			icon: "home",
			shortcut: ["h"],
			description: "Command",
			link: {
				internal: "/",
			},
		},
		{
			label: "Appearance",
			id: "appearance-divider",
			hoverable: false,
		},
		{
			label: "Themes",
			id: "themes",
			icon: resolvedTheme === "light" ? "sun" : "moon",
			description: "Choices",
			shortcut: ["t"],
			singleton: false,
			sublist: {
				key: "themes",
				list: [
					resolvedTheme === "light"
						? {
								label: "Dark",
								id: "darktheme",
								shortcut: ["d"],
								description: "Command",
								icon: "moon",
								action: () => setTheme("dark"),
							}
						: {
								label: "Light",
								id: "lighttheme",
								shortcut: ["l"],
								description: "Command",
								icon: "sun",
								action: () => setTheme("light"),
							},
					{
						label: "Same as system",
						id: "systemtheme",
						shortcut: ["y"],
						description: "Command",
						icon: "monitor",
						action: () => setTheme("system"),
					},
				],
				placeholder: "Set theme to...",
			},
		},
		{
			label: "Search",
			id: "search-divider",
			hoverable: false,
		},
		{
			label: "Search Blog Posts",
			id: "search",
			icon: "search",
			shortcut: ["s"],
			description: "Command",
			singleton: false,
			action: () => {
				dispatch(updateKbarToSearch())
				trackEvent("searchBlogPosts", "kbar")
			},
			onInputChange: (query: string) => {
				dispatch(updateKbarSearchQuery(query))
			},
		},
		{
			label: "Actions",
			id: "actions-divider",
			hoverable: false,
		},
		{
			label: "Subscribe to Newsletter",
			id: "newletter",
			description: "Link",
			icon: "subscribe",
			color: "text-blue-500",
			bgColor: "bg-blue-100",
			bgDark: "dark:bg-blue-900",
			link: {
				external: "https://lists.lipeng.ac/subscription/form",
			},
		},
		{
			label: "Subscribe to RSS Feed",
			id: "rss",
			description: "Link",
			icon: "rss",
			color: "text-yellow-500",
			bgColor: "bg-yellow-100",
			bgDark: "dark:bg-yellow-900",
			link: {
				external: "https://lipeng.ac/feed",
			},
		},
		{
			label: "Join our Discord Server",
			id: "discord",
			description: "Link",
			icon: "chatRounded",
			color: "text-purple-400",
			bgColor: "bg-purple-100",
			bgDark: "dark:bg-purple-900",
			link: {
				external: "https://discord.gg/TTwGnMgcxr",
			},
		},
		{
			label: "Sponsor My Work",
			id: "sponsor",
			description: "Page",
			icon: "love",
			color: "text-pink-500",
			bgColor: "bg-pink-100",
			bgDark: "dark:bg-pink-900",
			link: {
				internal: "/sponsor",
			},
		},
		{
			label: "Email Me",
			id: "email",
			description: "Link",
			icon: "email",
			link: {
				external: "mailto:tony.hlp@hotmail.com",
			},
		},
		{
			label: "Leave a Comment",
			id: "comment",
			description: "Page",
			icon: "comments",
			link: {
				internal: "/page/249",
			},
		},
		{
			label: "Ask me Anything",
			id: "ama",
			description: "Page",
			icon: "question",
			link: {
				internal: "/page/765",
			},
		},
		{
			label: "Pages",
			id: "pages-divider",
			hoverable: false,
		},
		{
			label: "About",
			id: "about",
			description: "Page",
			icon: "me",
			link: {
				internal: "/post/126",
			},
		},
		{
			label: "Dashboard",
			id: "dashboard",
			description: "Page",
			icon: "ppt",
			link: {
				internal: "/dashboard",
			},
		},
		{
			label: "Friends",
			id: "links",
			description: "Page",
			icon: "people",
			link: {
				internal: "/friends",
			},
		},
		{
			label: "Reading List",
			id: "reading-list",
			description: "Page",
			icon: "bookOpen",
			link: {
				internal: "/reading-list",
			},
		},
		{
			label: "Podcasts",
			id: "podcasts",
			description: "Page",
			icon: "microphone",
			link: {
				internal: "/podcasts",
			},
		},
		{
			label: "Links",
			id: "links-divider",
			hoverable: false,
		},
		{
			label: "Analytics",
			id: "analytics",
			description: "Link",
			icon: "growth",
			link: {
				external: "https://analytics.ouorz.com/share/E4O9QpCn/ouorz-next",
			},
		},
		{
			label: "Thoughts",
			id: "thoughts",
			description: "Link",
			icon: "lightBulb",
			link: {
				external: "https://notion.ouorz.com",
			},
		},
		{
			label: "Podcast",
			id: "podcast",
			description: "Link",
			icon: "mic",
			link: {
				external: "https://kukfm.com",
			},
		},
		{
			label: "Snapod",
			id: "snapod",
			description: "Link",
			icon: "microphone",
			link: {
				external: "https://www.snapodcast.com",
			},
		},
		{
			label: "Social",
			id: "social-divider",
			hoverable: false,
		},
		{
			label: "X",
			id: "twitter",
			description: "Link",
			icon: "twitterX",
			link: {
				external: "https://x.com/ttttonyhe",
			},
		},
		{
			label: "GitHub",
			id: "github",
			description: "Link",
			icon: "github",
			link: {
				external: "https://github.com/ttttonyhe",
			},
		},
		{
			label: "LinkedIn",
			id: "linkedin",
			description: "Link",
			icon: "briefCase",
			link: {
				external: "https://www.linkedin.com/in/~lhe",
			},
		},
		{
			label: "Web 3.0",
			id: "web3-divider",
			hoverable: false,
		},
		{
			label: "OpenSea",
			id: "opensea",
			description: "Link",
			icon: "openSea",
			link: {
				external: "https://opensea.io/ttttonyhe",
			},
		},
		{
			label: "MagicEden",
			id: "magicEden",
			description: "Link",
			icon: "magicEden",
			link: {
				external: "https://magiceden.io/u/tonyhe",
			},
		},
		{
			label: "Ethereum",
			id: "ens",
			description: "ttttonyhe.eth",
			icon: "eth",
			link: {
				external:
					"https://app.ens.domains/address/0x2650f08Da54F7019f9a3306bad0dfc8474644eAD",
			},
		},
		{
			label: "Solana",
			id: "solana",
			description: "tonyhe.sol",
			icon: "solana",
			link: {
				external: "https://naming.bonfida.org/#/domain/tonyhe",
			},
		},
	]

	const scrollHandler = (position: number) => {
		if (!headerRef?.current) return

		headerRef.current.style.transform = `translateY(${15 - position || 0}%)`
	}

	return (
		<ScrollWrapper handler={scrollHandler} startPosition={0} endPosition={15}>
			<header
				ref={headerRef}
				id="header"
				className="header fixed top-0 z-50 grid h-auto w-full grid-cols-8 border-b border-gray-200 px-1 py-2 leading-14 duration-300 dark:border-b-transparent dark:backdrop-blur-lg lg:border-0 lg:bg-transparent lg:px-5 lg:py-4">
				<div className="col-start-1 col-end-3 flex items-center lg:items-baseline lg:space-x-2">
					<Tabs items={leftTabItems} />
				</div>
				<OffsetTransition disabled={!nonHomePage} componentRef={titleRef}>
					<div
						ref={titleRef}
						className="col-start-3 col-end-7 flex items-center justify-center">
						{nonHomePage ? (
							<HeaderTitleComponent />
						) : (
							<HeaderSearchBarComponent
								activateKbar={() => dispatch(activateKbar(kbarItems))}
							/>
						)}
					</div>
				</OffsetTransition>
				<div className="col-start-7 col-end-9 flex justify-end space-x-2">
					<Tabs items={rightTabItems} />
				</div>
				<Kbar list={kbarItems} />
			</header>
		</ScrollWrapper>
	)
}

const Header = () => {
	const headerRef = useRef<HTMLDivElement>(null)

	return (
		<HeaderTransition componentRef={headerRef}>
			<HeaderComponent headerRef={headerRef} />
		</HeaderTransition>
	)
}

export default Header
