'use client'

import React, { useRef } from 'react'
import Link from 'next/link'
import Image from 'next/image'
import { usePathname, useRouter } from 'next/navigation'
import { useTheme } from 'next-themes'
import { useDispatch } from '~/hooks'
import { updateKbarToSearch, activateKbar } from '~/store/kbar/actions'
import { HeaderTransition, OffsetTransition } from '../Motion'
import Tabs from '../Tabs'
import Kbar from '../Kbar'
import useAnalytics from '~/hooks/analytics'

const Header = () => {
	const { setTheme, resolvedTheme } = useTheme()
	const router = useRouter()
	const pathName = usePathname()
	const dispatch = useDispatch()
	const headerRef = useRef<HTMLDivElement>(null)
	const titleRef = useRef<HTMLDivElement>(null)
	const { trackEvent } = useAnalytics()

	const TitleComponent = () => (
		<div
			ref={titleRef}
			className="col-start-3 col-end-5 justify-center opacity-0 flex items-center"
		>
			<div className="cursor-pointer mx-auto hidden lg:flex space-x-3 items-center justify-center">
				<div className="flex items-center flex-shrink-0 h-7 w-7 border rounded-full border-gray-300 dark:border-gray-500 -mt-0.5">
					<a
						href="https://opensea.io/assets/ethereum/0xe0be388ab81c47b0f098d2030a1c9ef190691a8a/2754"
						target="_blank"
						rel="noreferrer"
						className="flex"
					>
						<Image
							className="rounded-full"
							src="/tony.png"
							alt="Tony's NFT avatar"
							height={18.77}
							width={18.77}
						/>
					</a>
				</div>
				<div className="text-2 font-medium text-black">
					<Link href="/" passHref>
						<h3 className="text-gray-700 dark:text-gray-300">TonyHe</h3>
					</Link>
				</div>
			</div>
		</div>
	)

	const HeaderComponent = () => {
		const leftTabItems = [
			{
				label: 'Newsletter',
				icon: 'subscribe',
				link: {
					external: 'https://buttondown.email/helipeng',
				},
			},
			{
				label: 'Command+K',
				className: 'hidden lg:block',
				hoverable: false,
				component: (
					<div
						data-oa="click-activateKbar"
						data-cy="cmdkbutton"
						className="py-1 px-5 rounded-md cursor-pointer focus:outline-none justify-center items-center text-xl tracking-wider flex lg:flex"
						onClick={() => dispatch(activateKbar(kbarItems))}
					>
						<div
							aria-label="Command + K to Open Command Palette"
							className="bg-gray-100 dark:bg-transparent dark:border-gray-600 px-1.5 py-0.5 text-xs border rounded-md"
						>
							⌘+K
						</div>
					</div>
				),
			},
		]

		const rightTabItems = [
			pathName.split('/').length > 2
				? {
						label: 'Home',
						className: 'hidden lg:block',
						icon: 'home',
						link: {
							internal: '/',
						},
				  }
				: {
						label: 'Web 3',
						className: 'hidden lg:block',
						color: 'text-pink-500',
						bgColor: 'bg-pink-100',
						bgDark: 'dark:bg-pink-900',
						icon: 'rainbow',
						link: {
							internal: '/web3',
						},
				  },
			{
				label: 'Pages',
				className: 'hidden lg:block',
				icon: 'pages',
				link: {
					internal: '/pages',
				},
			},
			{
				label: 'About',
				icon: 'me',
				link: {
					internal: '/post/126',
				},
			},
		]

		const kbarItems = [
			{
				label: 'Navigation',
				id: 'navigation-divider',
				hoverable: false,
			},
			{
				label: 'Go Back',
				id: 'back',
				icon: 'left',
				shortcut: ['b'],
				action: () => router.back(),
				description: 'Command',
			},
			{
				label: 'Home',
				id: 'home',
				icon: 'home',
				shortcut: ['h'],
				description: 'Command',
				link: {
					internal: '/',
				},
			},
			{
				label: 'Appearance',
				id: 'appearance-divider',
				hoverable: false,
			},
			{
				label: 'Themes',
				id: 'themes',
				icon: resolvedTheme === 'light' ? 'sun' : 'moon',
				description: 'Choices',
				shortcut: ['t'],
				singleton: false,
				sublist: {
					key: 'themes',
					list: [
						resolvedTheme === 'light'
							? {
									label: 'Dark',
									id: 'darktheme',
									shortcut: ['d'],
									description: 'Command',
									icon: 'moon',
									action: () => setTheme('dark'),
							  }
							: {
									label: 'Light',
									id: 'lighttheme',
									shortcut: ['l'],
									description: 'Command',
									icon: 'sun',
									action: () => setTheme('light'),
							  },
						{
							label: 'Same as system',
							id: 'systemtheme',
							shortcut: ['y'],
							description: 'Command',
							icon: 'monitor',
							action: () => setTheme('system'),
						},
					],
					placeholder: 'Set theme to...',
				},
			},
			{
				label: 'Search',
				id: 'search-divider',
				hoverable: false,
			},
			{
				label: 'Search Blog Posts',
				id: 'search',
				icon: 'search',
				shortcut: ['s'],
				action: () => {
					dispatch(updateKbarToSearch())
					trackEvent('searchBlogPosts', 'kbar')
				},
				description: 'Command',
				singleton: false,
			},
			{
				label: 'Actions',
				id: 'actions-divider',
				hoverable: false,
			},
			{
				label: 'Subscribe to Newsletter',
				id: 'newletter',
				description: 'Link',
				icon: 'subscribe',
				color: 'text-blue-500',
				bgColor: 'bg-blue-100',
				bgDark: 'dark:bg-blue-900',
				link: {
					external: 'https://buttondown.email/helipeng',
				},
			},
			{
				label: 'Join Discord Server',
				id: 'discord',
				description: 'Link',
				icon: 'chatRounded',
				color: 'text-purple-400',
				bgColor: 'bg-purple-100',
				bgDark: 'dark:bg-purple-900',
				link: {
					external: 'https://discord.gg/TTwGnMgcxr',
				},
			},
			{
				label: 'Sponsor Me',
				id: 'sponsor',
				description: 'Page',
				icon: 'love',
				color: 'text-pink-500',
				bgColor: 'bg-pink-100',
				bgDark: 'dark:bg-pink-900',
				link: {
					internal: '/sponsor',
				},
			},
			{
				label: 'Email Me',
				id: 'email',
				description: 'Link',
				icon: 'email',
				link: {
					external: 'mailto:tony.hlp@hotmail.com',
				},
			},
			{
				label: 'Leave a Comment',
				id: 'comment',
				description: 'Page',
				icon: 'comments',
				link: {
					internal: '/page/249',
				},
			},
			{
				label: 'Ask me Anything',
				id: 'ama',
				description: 'Page',
				icon: 'question',
				link: {
					internal: '/page/765',
				},
			},
			{
				label: 'Pages',
				id: 'pages-divider',
				hoverable: false,
			},
			{
				label: 'About',
				id: 'about',
				description: 'Page',
				icon: 'me',
				link: {
					internal: '/post/126',
				},
			},
			{
				label: 'Dashboard',
				id: 'dashboard',
				description: 'Page',
				icon: 'ppt',
				link: {
					internal: '/dashboard',
				},
			},
			{
				label: 'Friends',
				id: 'links',
				description: 'Page',
				icon: 'people',
				link: {
					internal: '/friends',
				},
			},
			{
				label: 'Links',
				id: 'links-divider',
				hoverable: false,
			},
			{
				label: 'Analytics',
				id: 'analytics',
				description: 'Link',
				icon: 'growth',
				link: {
					external: 'https://analytics.ouorz.com/share/E4O9QpCn/ouorz-next',
				},
			},
			{
				label: 'Thoughts',
				id: 'thoughts',
				description: 'Link',
				icon: 'lightBulb',
				link: {
					external: 'https://notion.ouorz.com',
				},
			},
			{
				label: 'Podcast',
				id: 'podcast',
				description: 'Link',
				icon: 'mic',
				link: {
					external: 'https://kukfm.com',
				},
			},
			{
				label: 'Snapod',
				id: 'snapod',
				description: 'Link',
				icon: 'microphone',
				link: {
					external: 'https://www.snapodcast.com',
				},
			},
			{
				label: 'Social',
				id: 'social-divider',
				hoverable: false,
			},
			{
				label: 'Twitter',
				id: 'twitter',
				description: 'Link',
				icon: 'twitter',
				link: {
					external: 'https://twitter.com/ttttonyhe',
				},
			},
			{
				label: 'GitHub',
				id: 'github',
				description: 'Link',
				icon: 'github',
				link: {
					external: 'https://github.com/HelipengTony',
				},
			},
			{
				label: 'LinkedIn',
				id: 'linkedin',
				description: 'Link',
				icon: 'briefCase',
				link: {
					external: 'https://www.linkedin.com/in/lipenghe',
				},
			},
			{
				label: 'Web 3.0',
				id: 'web3-divider',
				hoverable: false,
			},
			{
				label: 'OpenSea',
				id: 'opensea',
				description: 'Link',
				icon: 'openSea',
				link: {
					external: 'https://opensea.io/ttttonyhe',
				},
			},
			{
				label: 'MagicEden',
				id: 'magicEden',
				description: 'Link',
				icon: 'magicEden',
				link: {
					external: 'https://magiceden.io/u/tonyhe',
				},
			},
			{
				label: 'Ethereum',
				id: 'ens',
				description: 'ttttonyhe.eth',
				icon: 'eth',
				link: {
					external:
						'https://app.ens.domains/address/0x2650f08Da54F7019f9a3306bad0dfc8474644eAD',
				},
			},
			{
				label: 'Solana',
				id: 'solana',
				description: 'tonyhe.sol',
				icon: 'solana',
				link: {
					external: 'https://naming.bonfida.org/#/domain/tonyhe',
				},
			},
		]

		return (
			<header
				ref={headerRef}
				id="header"
				className="leading-14 lg:border-0 border-b dark:border-b-transparent border-gray-200 lg:bg-transparent dark:backdrop-blur-lg duration-300 grid grid-cols-6 fixed top-0 h-auto w-full lg:py-4 lg:px-5 py-2 px-1 z-50"
			>
				<div className="col-start-1 col-end-2 flex lg:space-x-2">
					<Tabs items={leftTabItems} />
				</div>
				<OffsetTransition componentRef={titleRef}>
					<TitleComponent />
				</OffsetTransition>
				<div className="col-start-5 col-end-7 flex space-x-2 justify-end">
					<Tabs items={rightTabItems} />
				</div>
				<Kbar list={kbarItems} />
			</header>
		)
	}

	return (
		<HeaderTransition componentRef={headerRef}>
			<HeaderComponent />
		</HeaderTransition>
	)
}

export default Header
