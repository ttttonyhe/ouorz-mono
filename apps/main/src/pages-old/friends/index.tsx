import Head from 'next/head'
import React from 'react'
import Link from 'next/link'
import Image from 'next/image'
import { GetStaticProps } from 'next'
import { NextPageWithLayout } from '~/pages-old/_app'
import { pageLayout } from '~/components/Page'
import trimStr from '~/utilities/trimString'
import getApi from '~/utilities/api'
import { Icon } from '@twilight-toolkit/ui'
import { GlowingBackground } from '~/components/Visual'

const Friends: NextPageWithLayout = ({ friends }: { friends: any }) => {
	return (
		<div>
			<Head>
				<title>Friends - TonyHe</title>
				<link
					rel="icon"
					href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🧑‍🤝‍🧑</text></svg>"
				/>
				<meta name="description" content="TonyHe's friends' sites" />
			</Head>
			<div className="lg:mt-20 mt-0 lg:pt-0 pt-24">
				<div className="mb-4 flex items-center">
					<div className="flex-1 items-center">
						<h1 className="font-medium text-1 text-black dark:text-white tracking-wide">
							<span className="hover:animate-spin inline-block cursor-pointer mr-3">
								🧑‍🤝‍🧑
							</span>
							Friends
						</h1>
					</div>
					<div className="h-full flex justify-end whitespace-nowrap items-center mt-2">
						<div className="flex-1 px-5">
							<p className="text-xl text-gray-500 dark:text-gray-400">
								<Link href="/" className="flex items-center">
									<span className="w-6 h-6 mr-2">
										<Icon name="left" />
									</span>
									Home
								</Link>
							</p>
						</div>
					</div>
				</div>
				<div className="border shadow-sm w-full py-3 px-5 flex rounded-md bg-white dark:bg-gray-800 dark:border-gray-800 items-center my-2">
					<p className="text-xl tracking-wide text-gray-500 dark:text-gray-400 items-center">
						Email me at tony.hlp#hotmail.com for link exchange
					</p>
				</div>
			</div>
			<div
				className="mt-5 grid grid-cols-2 gap-4 glowing-area"
				data-cy="friendsItems"
			>
				{friends.map((item, index) => {
					return (
						<div
							className="glowing-div cursor-pointer hover:shadow-md transition-shadow shadow-sm border bg-white dark:bg-gray-800 dark:border-gray-800 items-center rounded-md"
							key={index}
						>
							<GlowingBackground />
							<div className="glowing-div-content px-6 py-4 w-fullitems-center flex-1">
								<a href={item.post_metas.link} target="_blank" rel="noreferrer">
									<h1 className="flex items-center text-2xl tracking-wide font-medium mb-0.5">
										<Image
											alt={item.post_title}
											src={item.post_img.url}
											width={20}
											height={20}
											className="rounded-full border border-gray-200 dark:border-gray-500"
										/>
										<span className="ml-2">{item.post_title}</span>
									</h1>
									<p
										className="text-4 text-gray-500 dark:text-gray-400 tracking-wide whitespace-nowrap overflow-hidden text-ellipsis"
										dangerouslySetInnerHTML={{
											__html: trimStr(item.post_excerpt.four, 150),
										}}
									/>
								</a>
							</div>
						</div>
					)
				})}
			</div>
		</div>
	)
}

Friends.layout = pageLayout

export const getStaticProps: GetStaticProps = async () => {
	const resCount = await fetch(
		getApi({
			count: true,
		})
	)
	const dataCount = await resCount.json()
	const count: number = dataCount.count

	const res = await fetch(
		getApi({
			cate: '2',
			perPage: count,
		})
	)
	const data = await res.json()

	return {
		revalidate: 24 * 60 * 60,
		props: {
			friends: data,
		},
	}
}

export default Friends
