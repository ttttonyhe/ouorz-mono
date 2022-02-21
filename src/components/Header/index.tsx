import Button from '~/components/Button'
import React, { useState, useRef } from 'react'
import Link from 'next/link'
import Image from 'next/image'
import { useRouter } from 'next/router'
import Search from '~/components/Search'
import { HeaderTransition, OffsetTransition } from '../Motion'
import Tabs from '../Tabs'

const Header = () => {
	const router = useRouter()

	const [startSearching, setStartSearching] = useState<boolean>(false)
	const [endSearching, setEndSearching] = useState<boolean>(false)
	const headerRef = useRef<HTMLDivElement>(null)
	const titleRef = useRef<HTMLDivElement>(null)

	const TitleComponent = () => (
		<div
			ref={titleRef}
			className="col-start-3 col-end-5 items-center justify-center pt-1 opacity-0"
		>
			<div className="cursor-pointer mx-auto hidden lg:flex space-x-3 items-center justify-center">
				<div className="flex-shrink-0 h-7 w-7 border rounded-full border-gray-300 dark:border-gray-500">
					<Image
						className="rounded-full"
						src="/tony.jpg"
						alt="tony's logo"
						height={18.77}
						width={18.77}
						layout="fixed"
					/>
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
				label: 'Search',
				icon: 'search',
				className: 'hidden lg:block',
				onClick: () => setStartSearching(true),
			},
		]

		const rightTabItems = [
			router.asPath.split('/').length > 2
				? {
						label: 'Home',
						className: 'hidden lg:block',
						icon: 'home',
						link: {
							internal: '/',
						},
				  }
				: {
						label: 'Sponsor',
						className: 'hidden lg:block',
						color: 'text-pink-500',
						bgColor: 'bg-pink-100',
						bgDark: 'dark:bg-pink-900',
						icon: 'love',
						link: {
							internal: '/sponsor',
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

		return (
			<header
				ref={headerRef}
				id="header"
				className="leading-14 lg:border-0 border-b border-gray-200 dark:border-gray-800 lg:bg-transparent bg-white duration-300 grid grid-cols-6 fixed top-0 h-auto w-full lg:py-4 lg:px-5 py-2 px-1 z-10"
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
				<Search
					startSearching={startSearching}
					setStartSearching={setStartSearching}
					setEndSearching={setEndSearching}
					endSearching={endSearching}
				/>
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
