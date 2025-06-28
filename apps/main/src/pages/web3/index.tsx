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

const NFTs = React.lazy(() => import("~/components/Grids/NFTs"))

const Web3: NextPageWithLayout = ({ sponsors }: { sponsors: any }) => {
	return (
		<div>
			<Head>
				<title>Web 3.0 - Tony He</title>
				<link
					rel="icon"
					href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🌈</text></svg>"
				/>
				<meta name="description" content="Tony's Web 3.0 Profile" />
			</Head>
			<div className="glowing-area">
				<div className="mt-0 pt-24 lg:mt-20 lg:pt-0">
					<div className="mb-4 flex items-center">
						<div className="flex flex-1 items-center">
							<div className="mt-1 mr-4.5 flex -rotate-6 cursor-pointer items-center">
								<span className="text-[35px] drop-shadow-lg hover:animate-spin">
									🌈
								</span>
							</div>
							<div>
								<h2 className="flex items-center gap-x-1.5 text-[28px] font-medium tracking-wide text-black dark:text-white">
									Web 3.0{" "}
									<span className="rounded-full border border-green-300 bg-green-50 px-2 py-0.5 text-xs text-green-500 dark:border-green-700 dark:bg-green-800 dark:text-green-400">
										Beta
									</span>
								</h2>
								<p className="-mt-1 text-sm text-neutral-500 dark:text-gray-400">
									Wallets, Identities and Assets
								</p>
							</div>
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
				</div>
				<div className="my-5">
					<hr className="dark:border-gray-600" />
				</div>
				<div className="mb-10">
					<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 pt-[4px] pb-1 font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5 text-yellow-500">
							<Icon name="bitcoin" />
						</span>
						<span className="uppercase">Bitcoin</span>
					</label>
					<div className="mt-4">
						<div className="mb-4">
							<PageCard
								title="Native SegWit"
								icon="wallet1"
								des="bc1q6w0znvyyuag7egzgfke5j8f6f7r8laskfqstk4"
								className="text-black dark:text-white"
							/>
						</div>
						<div className="grid grid-cols-2 gap-4">
							<PageCard
								title="SegWit"
								iconSmall="wallet2"
								des="35Mb9GbWn5FWPU19X2EBvp9VR9cyZvDbG9"
								className="text-black dark:text-white"
								wrappable={true}
							/>
							<PageCard
								title="Legacy"
								iconSmall="wallet3"
								des="1N3gofgiLDF3oCrFiqRxpALSH2CeauSq6V"
								className="text-black dark:text-white"
								wrappable={true}
							/>
						</div>
					</div>
				</div>
				<div className="mb-10">
					<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 pt-[4px] pb-1 pl-3 font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-[4px] flex h-5 w-5 text-blue-500">
							<Icon name="eth" />
						</span>
						<span className="uppercase">Ethereum</span>
					</label>
					<div className="mt-4">
						<div className="flex flex-col gap-4 lg:flex-row">
							<div className="flex-1">
								<PageCard
									title="Wallet Address"
									iconSmall="wallet1"
									des="0x2650f08Da54F7019f9a3306bad0dfc8474644eAD"
									href="https://etherscan.io/address/0x2650f08Da54F7019f9a3306bad0dfc8474644eAD"
									className="text-black dark:text-white"
									wrappable={true}
								/>
							</div>
							<div className="flex-1">
								<PageCard
									title="Name Service"
									iconSmall="fingerprint"
									des="(ENS) ttttonyhe.eth"
									href="https://app.ens.domains/address/0x2650f08Da54F7019f9a3306bad0dfc8474644eAD"
									className="text-black dark:text-white"
									wrappable={true}
								/>
							</div>
						</div>
					</div>
				</div>
				<div className="mb-10">
					<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 pt-[4px] pb-1 font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5 text-purple-500">
							<Icon name="solana" />
						</span>
						<span className="uppercase">Solana</span>
					</label>
					<div className="mt-4">
						<div className="flex flex-col gap-4 lg:flex-row">
							<div className="flex-1">
								<PageCard
									title="Wallet Address"
									iconSmall="wallet1"
									des="G9T9yXeWLyspA9xLSLwZYvAPbSNV9E2NU7jWLpQDW6Re"
									href="https://solscan.io/account/G9T9yXeWLyspA9xLSLwZYvAPbSNV9E2NU7jWLpQDW6Re"
									className="text-black dark:text-white"
									wrappable={true}
								/>
							</div>
							<div className="flex-1">
								<PageCard
									title="Name Service"
									iconSmall="fingerprint"
									des="(Bonfida) tonyhe.sol"
									href="https://naming.bonfida.org/#/domain/tonyhe"
									className="text-black dark:text-white"
									wrappable={true}
								/>
							</div>
						</div>
					</div>
				</div>
				<div className="mb-10">
					<hr className="dark:border-gray-600" />
				</div>
				<div className="mb-10">
					<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 pt-[4px] pb-1 font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
							<Icon name="collection" />
						</span>
						<span className="uppercase">Non-fungible Tokens (NFTs)</span>
					</label>
					<div className="mt-4">
						<NFTs />
					</div>
				</div>
				<div className="mb-10">
					<hr className="dark:border-gray-600" />
				</div>
				<div className="mb-28">
					<label className="inline-flex items-center rounded-tl-xl rounded-tr-xl border border-gray-300 bg-white px-4 pt-[4px] pb-1 font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5 text-pink-500">
							<Icon name="love" />
						</span>
						<span className="uppercase">Sponsor Me</span>
					</label>
					<div className="my-2 mb-4 flex w-full items-center rounded-br-xl rounded-bl-xl border border-gray-300 bg-white px-4 py-3 shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<p className="items-center text-xl tracking-wide">
							I am developing and maintaining various open source projects and
							hosting a podcast about tech, life and career:
						</p>
					</div>
					<div className="mb-10">
						<div className="mb-10 grid grid-cols-2 gap-4">
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
					</div>
					<div className="my-2 mb-4 flex w-full items-center rounded-tl-xl rounded-tr-xl border border-gray-300 bg-white px-4 py-3 shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<p className="items-center text-xl tracking-wide">
							If you found my projects interesting or helpful, please consider
							supporting me through the following ways:
						</p>
					</div>
					<div className="mt-4 mb-10">
						<div className="mb-4">
							<PageCard
								title="Github Sponsors"
								des="ttttonyhe"
								icon="love"
								className="text-pink-600"
								href="https://github.com/sponsors/ttttonyhe"
							/>
						</div>
						<div className="grid grid-cols-2 gap-4">
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
					</div>
					<div className="my-2 mb-4 flex w-full items-center rounded-tl-xl rounded-tr-xl border border-gray-300 bg-white px-4 py-3 shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<p className="items-center text-xl tracking-wide">
							Contact me after finishing your payment, and I{"'"}ll put your
							name on the list below:
						</p>
					</div>
					<div className="grid grid-cols-2 gap-4" data-cy="sponsorsItems">
						{sponsors.map((item: any, index: number) => {
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
		</div>
	)
}

Web3.layout = pageLayout

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

export default Web3
