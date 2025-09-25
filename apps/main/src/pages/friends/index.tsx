/* eslint-disable react/no-unescaped-entities */
import { Icon } from "@twilight-toolkit/ui"
import type { GetStaticProps } from "next"
import Head from "next/head"
import Image from "next/image"
import Link from "next/link"
import { pageLayout } from "~/components/Page"
import { GlowingBackground } from "~/components/Visual"
import type { NextPageWithLayout } from "~/pages/_app"
import getAPI from "~/utilities/api"
import { trimStr } from "~/utilities/string"

const Friends: NextPageWithLayout = ({ friends }: { friends: any }) => {
	return (
		<div>
			<Head>
				<title>Friends - Tony He</title>
				<link
					rel="icon"
					href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🧑‍🤝‍🧑</text></svg>"
				/>
				<meta name="description" content="TonyHe's friends' sites" />
			</Head>
			<div className="mt-0 pt-24 lg:mt-20 lg:pt-0">
				<div className="mb-4 flex items-center">
					<div className="flex-1 items-center">
						<h1 className="font-medium text-1 text-black tracking-wide dark:text-white">
							<span className="mr-3 inline-block cursor-pointer hover:animate-spin">
								🧑‍🤝‍🧑
							</span>
							Friends
						</h1>
					</div>
					<div className="mt-2 flex h-full items-center justify-end whitespace-nowrap">
						<div className="flex-1 px-5">
							<p className="text-gray-500 text-xl dark:text-gray-400">
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
					<p className="items-center text-gray-500 text-xl tracking-wide dark:text-gray-400">
						To join this webring, email me at ABC_tony.hlp@hotmail.com (with the
						leading "ABC_" removed).
					</p>
				</div>
			</div>
			<div
				className="glowing-area mt-5 grid grid-cols-2 gap-4"
				data-cy="friendsItems">
				{friends.map((item, index) => {
					return (
						<div
							className="glowing-div cursor-pointer items-center rounded-md border bg-white shadow-xs transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-800"
							key={index}>
							<GlowingBackground />
							<div className="glowing-div-content w-fullitems-center flex-1 px-6 py-4">
								<a href={item.post_metas.link} target="_blank" rel="noreferrer">
									<h1 className="mb-0.5 flex items-center font-medium text-2xl tracking-wide">
										<Image
											alt={item.post_title}
											src={item.post_img.url}
											width={20}
											height={20}
											className="rounded-full border border-gray-200 dark:border-gray-500"
											loading="lazy"
										/>
										<span className="ml-2">{item.post_title}</span>
									</h1>
									<p
										className="overflow-hidden text-ellipsis whitespace-nowrap text-4 text-gray-500 tracking-wide dark:text-gray-400"
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
	const resCount = await fetch(getAPI("internal", "postStats"))
	const dataCount = await resCount.json()
	const count: number = dataCount.count

	const res = await fetch(
		getAPI("internal", "posts", {
			cate: 2,
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
