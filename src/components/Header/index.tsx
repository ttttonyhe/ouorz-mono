import Button from '~/components/Button'
import React, { useState, useRef } from 'react'
import Link from 'next/link'
import Image from 'next/image'
import { useRouter } from 'next/router'
import Search from '~/components/Search'
import { BoxShadowTransition, OffsetTransition } from '../Motion'

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

	const HeaderComponent = () => (
		<header
			ref={headerRef}
			id="header"
			className="leading-14 lg:border-0 border-b border-gray-200 dark:border-gray-800 transition-all lg:bg-transparent bg-white duration-300 grid grid-cols-6 fixed top-0 h-auto w-full lg:py-4 lg:px-5 py-2 px-1 z-10"
		>
			<div className="col-start-1 col-end-2 flex lg:space-x-2">
				<a
					href="https://buttondown.email/helipeng"
					target="_blank"
					rel="noreferrer"
				>
					<Button
						bType="menu-default"
						icon="subscribe"
						className="hidden lg:flex text-3"
					>
						Newsletter
					</Button>
				</a>
				<Button
					bType="menu-default"
					icon="search"
					className="text-3"
					onClick={() => {
						setStartSearching(!startSearching)
					}}
				>
					Search
				</Button>
			</div>
			<OffsetTransition componentRef={titleRef}>
				<TitleComponent />
			</OffsetTransition>
			<div className="col-start-5 col-end-7 flex space-x-2 justify-end">
				{router.asPath.split('/').length > 2 ? (
					<Link href="/">
						<a>
							<Button
								bType="menu-default"
								icon="home"
								className="text-3 hidden lg:flex"
							>
								Home
							</Button>
						</a>
					</Link>
				) : (
					<Link href="/sponsor">
						<a>
							<Button
								bType="menu-primary"
								icon="love"
								className="text-pink-500 hidden lg:flex text-3"
							>
								Sponsor
							</Button>
						</a>
					</Link>
				)}
				<Link href="/pages">
					<a>
						<Button
							bType="menu-default"
							icon="pages"
							className="hidden lg:flex text-3"
						>
							Pages
						</Button>
					</a>
				</Link>
				<Link href="/post/126">
					<a>
						<Button bType="menu-default" icon="me" className="text-3">
							About
						</Button>
					</a>
				</Link>
			</div>
			<Search
				startSearching={startSearching}
				setStartSearching={setStartSearching}
				setEndSearching={setEndSearching}
				endSearching={endSearching}
			/>
		</header>
	)

	return (
		<BoxShadowTransition componentRef={headerRef}>
			<HeaderComponent />
		</BoxShadowTransition>
	)
}

export default Header
