import PageCard from '~/components/Card/Page'
import getApi from '~/utilities/api'
import { GlowingBackground } from '~/components/Visual'

type Sponsor = {
	name: string
	date: string
	unit: string
	amount: number
}

/**
 * Get sponsors data from WP Custom API
 *
 * @return {*}  {Promise<WPPost[]>}
 */
export const getSponsors = async (): Promise<Sponsor[]> => {
	const res = await fetch(
		getApi({
			sponsor: true,
		}),
		{
			next: {
				revalidate: 5 * 24 * 60 * 60,
			},
		}
	)
	const data = await res.json()
	return data?.donors || []
}

const SponsorPage = async () => {
	const sponsors = await getSponsors()

	return (
		<div className="glowing-area">
			<div className="mt-5 mb-10 grid grid-cols-2 gap-4">
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
			<div className="border shadow-sm w-full p-7 rounded-md bg-white dark:bg-gray-800 dark:border-gray-800 items-center my-2 mb-10">
				<p className="text-xl tracking-wide text-gray-500 dark:text-gray-300 items-center">
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
						des="HelipengTony"
						icon="love"
						className="text-pink-600"
						href="https://github.com/sponsors/HelipengTony"
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
			<div className="border shadow-sm w-full py-3 px-5 flex rounded-md bg-white dark:bg-gray-800 dark:border-gray-800 items-center my-2">
				<p className="text-xl tracking-wide text-gray-500 dark:text-gray-400 items-center">
					Contact me after finishing your payment, and I{"'"}ll put your name on
					the list below
				</p>
			</div>
			<div className="mt-5 grid grid-cols-2 gap-4" data-cy="sponsorsItems">
				{sponsors.map((item, index) => {
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
	)
}

export default SponsorPage
