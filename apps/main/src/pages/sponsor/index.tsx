import { Icon } from "@twilight-toolkit/ui"
import { GetStaticProps } from "next"
import Head from "next/head"
import Link from "next/link"
import React from "react"
import PageCard from "~/components/Card/Page"
import { pageLayout } from "~/components/Page"
import { GlowingBackground } from "~/components/Visual"
import { NextPageWithLayout } from "~/pages/_app"
import getAPI from "~/utilities/api"

const Sponsor: NextPageWithLayout = ({ sponsors }: { sponsors: any }) => {
	return (
		<div>
			<Head>
				<title>Sponsor - Tony He</title>
				<link
					rel="icon"
					href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>☕</text></svg>"
				/>
				<meta name="description" content="Sponsor Tony's work" />
			</Head>
			<div className="glowing-area">
				<div className="mt-0 pt-24 lg:mt-20 lg:pt-0">
					<div className="mb-4 flex items-center">
						<div className="flex-1 items-center">
							<h1 className="text-1 font-medium tracking-wide text-black dark:text-white">
								<span className="mr-3 inline-block cursor-pointer hover:animate-spin">
									☕
								</span>
								Sponsor
							</h1>
						</div>
						<div className="mt-2 flex h-full items-center justify-end whitespace-nowrap">
							<div className="flex-1 px-5">
								<p className="text-xl text-gray-500 dark:text-gray-400">
									<Link href="/" className="flex items-center">
										<span className="mr-2 h-6 w-6">
											<Icon name="left" />
										</span>
										Home
									</Link>
								</p>
							</div>
						</div>
					</div>
					<div className="my-2 flex w-full items-center rounded-md border bg-white px-5 py-3 shadow-xs dark:border-gray-800 dark:bg-gray-800">
						<p className="items-center text-xl tracking-wide text-gray-500 dark:text-gray-400">
							I am developing and maintaining various open source projects and
							hosting a podcast about tech, life and career 🤓
						</p>
					</div>
				</div>
				<div className="mt-5 mb-10 grid grid-cols-2 gap-4">
					<PageCard
						title="Github"
						des="ttttonyhe"
						icon="githubLine"
						className="text-black dark:text-white"
						href="https://github.com/ttttonyhe"
					/>
					<PageCard
						title="Podcast"
						des="Known Unknowns"
						icon="mic"
						className="text-black dark:text-white"
						href="https://kukfm.com"
					/>
				</div>
				<div className="my-2 mb-10 w-full items-center rounded-md border bg-white p-7 shadow-xs dark:border-gray-800 dark:bg-gray-800">
					<p className="items-center text-xl tracking-wide text-gray-500 dark:text-gray-300">
						If you found my projects or podcast useful or interesting, please
						consider supporting me through the following ways:
					</p>
					<div className="mt-5 grid grid-cols-2 gap-4">
						<PageCard
							title="Alipay"
							des="tony.hlp@hotmail.com"
							icon="alipay"
							className="text-blue-500"
							href="https://static.ouorz.com/alipay.png"
						/>
						<PageCard
							title="Wechat Pay"
							des="ttttonyhe"
							icon="wxpay"
							className="text-green-600"
							href="https://static.ouorz.com/wechatpay.png"
						/>
					</div>
					<div className="mt-4 grid grid-cols-2 gap-4">
						<PageCard
							title="Github Sponsors"
							des="ttttonyhe"
							icon="love"
							className="text-pink-600"
							href="https://github.com/sponsors/ttttonyhe"
						/>
						<PageCard
							title="Bitcoin"
							des="BTC Network"
							icon="https://static.ouorz.com/bitcoin.png"
							href="https://static.ouorz.com/bitcoin.jpg"
						/>
					</div>
					<div className="mt-4 grid grid-cols-2 gap-4">
						<PageCard
							title="Solana"
							des="tonyhe.sol"
							icon="https://static.ouorz.com/sol.png"
						/>
						<PageCard
							title="Ethereum"
							des="ttttonyhe.eth"
							icon="https://static.ouorz.com/eth.png"
							href="https://static.ouorz.com/metamask.png"
						/>
					</div>
				</div>
				<div className="my-2 flex w-full items-center rounded-md border bg-white px-5 py-3 shadow-xs dark:border-gray-800 dark:bg-gray-800">
					<p className="items-center text-xl tracking-wide text-gray-500 dark:text-gray-400">
						Contact me after finishing your payment, and I{"'"}ll put your name
						on the list below
					</p>
				</div>
				<div className="mt-5 grid grid-cols-2 gap-4" data-cy="sponsorsItems">
					{sponsors.map((item, index) => {
						return (
							<div
								key={index}
								className="glowing-div flex cursor-pointer items-center rounded-md border bg-white px-5 py-4 shadow-xs transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-800">
								<GlowingBackground />
								<div className="glowing-div-content flex w-full items-center overflow-hidden text-ellipsis whitespace-nowrap">
									<h1 className="flex-1 items-center text-xl font-medium tracking-wide">
										{item.name}
									</h1>
									<p className="text-4 flex items-center justify-end tracking-wide text-gray-400">
										<span className="hidden lg:flex">
											{item.date}&nbsp;|&nbsp;
										</span>
										<span className="text-gray-700 dark:text-white">
											{item.unit}
											{item.amount}
										</span>
									</p>
								</div>
							</div>
						)
					})}
				</div>
			</div>
		</div>
	)
}

Sponsor.layout = pageLayout

export const getStaticProps: GetStaticProps = async () => {
	const res = await fetch(getAPI("internal", "sponsors"))
	const data = await res.json()

	if (!data) {
		return {
			notFound: true,
		}
	}

	return {
		revalidate: 5 * 24 * 60 * 60,
		props: {
			sponsors: data.donors,
		},
	}
}

export default Sponsor
