import Head from 'next/head'
import React from 'react'
import Link from 'next/link'
import useSWR from 'swr'
import { GetStaticProps } from 'next'
import fetcher from '~/lib/fetcher'
import Content from '~/components/Content'
import { Icon } from '@twilight-toolkit/ui'
import PageCard from '~/components/Card/Page'
import { NFTCard, NFTCardLoading } from '~/components/Card/NFT'
import { ResDataType } from '~/pages/api/nft'
import getApi from '~/utilities/api'
import { GlowingBackground } from '~/components/Visual'

const Web3 = ({ sponsors }: { sponsors: any }) => {
	const { data } = useSWR<ResDataType>('api/nft', fetcher)

	return (
		<div>
			<Head>
				<title>Web 3.0 - TonyHe</title>
				<link
					rel="icon"
					href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🌈</text></svg>"
				/>
				<meta name="description" content="Tony's Web 3.0 Profile" />
			</Head>
			<Content>
				<div className="glowing-area">
					<div className="lg:mt-20 mt-0 lg:pt-0 pt-24">
						<div className="mb-4 flex items-center">
							<div className="flex-1 flex items-center">
								<div className="flex items-center cursor-pointer mt-1 mr-4.5 -rotate-6">
									<i className="text-[35px] hover:animate-spin drop-shadow-lg">
										🌈
									</i>
								</div>
								<div>
									<h2 className="font-medium text-[28px] text-black dark:text-white tracking-wide flex items-center gap-x-1.5">
										Web 3.0{' '}
										<span className="text-xs py-0.5 px-2 text-green-500 dark:text-green-400 bg-green-50 dark:bg-green-800 rounded-full border border-green-300 dark:border-green-700">
											Beta
										</span>
									</h2>
									<p className="text-sm text-neutral-500 dark:text-gray-400 -mt-1">
										Wallets, Identities and Assets
									</p>
								</div>
							</div>
							<div className="h-full flex justify-end whitespace-nowrap items-center mt-2">
								<div className="flex-1 px-5">
									<p className="text-xl text-gray-500 dark:text-gray-400">
										<Link href="/">
											<a className="flex items-center">
												<span className="w-6 h-6 mr-2">
													<Icon name="left" />
												</span>
												Home
											</a>
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
						<label className="rounded-full bg-white dark:bg-gray-700 dark:border-gray-600 shadow-sm border border-gray-300 tracking-wider font-medium pb-1 pt-[4px] px-4 inline-flex items-center">
							<span className="w-5 h-5 flex mr-1.5 text-yellow-500">
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
						<label className="rounded-full bg-white dark:bg-gray-700 dark:border-gray-600 shadow-sm border border-gray-300 tracking-wider font-medium pb-1 pt-[4px] pl-3 px-4 inline-flex items-center">
							<span className="w-5 h-5 flex mr-[4px] text-blue-500">
								<Icon name="eth" />
							</span>
							<span className="uppercase">Ethereum</span>
						</label>
						<div className="mt-4">
							<div className="flex gap-4 lg:flex-row flex-col">
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
						<label className="rounded-full bg-white dark:bg-gray-700 dark:border-gray-600 shadow-sm border border-gray-300 tracking-wider font-medium pb-1 pt-[4px] px-4 inline-flex items-center">
							<span className="w-5 h-5 flex mr-1.5 text-purple-500">
								<Icon name="solana" />
							</span>
							<span className="uppercase">Solana</span>
						</label>
						<div className="mt-4">
							<div className="flex gap-4 lg:flex-row flex-col">
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
						<label className="rounded-full bg-white dark:bg-gray-700 dark:border-gray-600 shadow-sm border border-gray-300 tracking-wider font-medium pb-1 pt-[4px] px-4 inline-flex items-center">
							<span className="w-5 h-5 flex mr-1.5">
								<Icon name="collection" />
							</span>
							<span className="uppercase">Non-fungible Tokens (NFTs)</span>
						</label>
						<div className="mt-4">
							<div className="grid lg:grid-cols-3 grid-cols-2 gap-4">
								{!data && (
									<>
										<NFTCardLoading uniqueKey="nft-card-skeleton-1" />
										<NFTCardLoading uniqueKey="nft-card-skeleton-2" />
										<NFTCardLoading uniqueKey="nft-card-skeleton-3" />
									</>
								)}
								{data &&
									data.eth.map((item, index: React.Key) => {
										return (
											<NFTCard
												key={index}
												image={item.media[0].raw}
												title={item.title}
												description={item.description}
												tokenType={item.id.tokenMetadata.tokenType}
												blockchain="ethereum"
												contract={item.contract.address}
												link={item.tokenUri.raw}
											/>
										)
									})}
								{data &&
									data.sol.map((item, index: React.Key) => {
										return (
											<NFTCard
												key={index}
												image={item.imageUrl}
												title={item.name}
												description={item.description}
												blockchain="solana"
												token={item.tokenAddress}
											/>
										)
									})}
							</div>
						</div>
					</div>
					<div className="mb-10">
						<hr className="dark:border-gray-600" />
					</div>
					<div className="mb-10">
						<label className="rounded-tl-xl rounded-tr-xl bg-white dark:bg-gray-700 dark:border-gray-600 shadow-sm border border-gray-300 tracking-wider font-medium pb-1 pt-[4px] px-4 inline-flex items-center">
							<span className="w-5 h-5 flex mr-1.5 text-pink-500">
								<Icon name="love" />
							</span>
							<span className="uppercase">Sponsor Me</span>
						</label>
						<div className="mb-4 border border-gray-300 shadow-sm w-full py-3 px-4 flex rounded-bl-xl rounded-br-xl bg-white dark:bg-gray-700 dark:border-gray-600 items-center my-2">
							<p className="text-xl tracking-wide items-center">
								I am developing and maintaining various open source projects and
								hosting a podcast about tech, life and career:
							</p>
						</div>
						<div className="mb-10">
							<div className="mb-10 grid grid-cols-2 gap-4">
								<PageCard
									title="Github"
									des="HelipengTony"
									icon="githubLine"
									className="text-black dark:text-white"
									href="https://github.com/HelipengTony"
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
						<div className="mb-4 border border-gray-300 shadow-sm w-full py-3 px-4 flex rounded-tl-xl rounded-tr-xl bg-white dark:bg-gray-700 dark:border-gray-600 items-center my-2">
							<p className="text-xl tracking-wide items-center">
								If you found my projects interesting or helpful, please consider
								supporting me through the following ways:
							</p>
						</div>
						<div className="mt-4 mb-10">
							<div className="mb-4">
								<PageCard
									title="Github Sponsors"
									des="HelipengTony"
									icon="love"
									className="text-pink-600"
									href="https://github.com/sponsors/HelipengTony"
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
						<div className="mb-4 border border-gray-300 shadow-sm w-full py-3 px-4 flex rounded-tl-xl rounded-tr-xl bg-white dark:bg-gray-700 dark:border-gray-600 items-center my-2">
							<p className="text-xl tracking-wide items-center">
								Contact me after finishing your payment, and I{"'"}ll put your
								name on the list below:
							</p>
						</div>
						<div className="grid grid-cols-2 gap-4" data-cy="sponsorsItems">
							{sponsors.map((item: any, index: number) => {
								return (
									<div
										key={index}
										className="glowing-div cursor-pointer hover:shadow-md transition-shadow shadow-sm border py-4 px-5 bg-white dark:bg-gray-800 dark:border-gray-800 flex items-center rounded-md"
									>
										<GlowingBackground />
										<div className="glowing-div-content w-full flex items-center whitespace-nowrap overflow-hidden text-ellipsis">
											<h1 className="flex-1 items-center text-xl tracking-wide font-medium">
												{item.name}
											</h1>
											<p className="text-4 text-gray-400 tracking-wide justify-end items-center flex">
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
			</Content>
		</div>
	)
}

export const getStaticProps: GetStaticProps = async () => {
	const res = await fetch(
		getApi({
			sponsor: true,
		})
	)
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
