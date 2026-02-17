/* eslint-disable react/no-unescaped-entities */
import { Icon } from "@twilight-toolkit/ui"
import type { GetStaticProps } from "next"
import Head from "next/head"
import Image from "next/image"
import { useRouter } from "next/router"
import { pageLayout } from "~/components/Page"
import type { NextPageWithLayout } from "~/pages/_app"
import getAPI from "~/utilities/api"
import { trimStr } from "~/utilities/string"
import {
	getViewTransitionName,
	navigateWithTransition,
} from "~/utilities/viewTransition"

const Links: NextPageWithLayout = ({ friends }: { friends: any }) => {
	const router = useRouter()

	return (
		<div>
			<Head>
				<title>Links - Tony He</title>
				<link
					rel="icon"
					href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🧑‍🤝‍🧑</text></svg>"
				/>
				<meta name="description" content="TonyHe's friends' sites" />
			</Head>
			<section className="mt-0 pt-24 lg:mt-20 lg:pt-0">
				<div className="mb-4 flex items-center">
					<div className="flex flex-1 items-center">
						<div className="-rotate-6 mt-1 mr-4.5 flex cursor-pointer items-center">
							<span className="text-[35px] drop-shadow-lg hover:animate-spin">
								🔗
							</span>
						</div>
						<div>
							<h2 className="flex items-center gap-x-1.5 whitespace-nowrap font-medium text-[28px] text-black tracking-wide dark:text-white">
								<span
									style={{
										viewTransitionName: getViewTransitionName("Links"),
									}}>
									Links
								</span>
							</h2>
							<p className="-mt-1 text-neutral-500 text-sm dark:text-gray-400">
								A collection of fun stuff and links from the internet.
							</p>
						</div>
					</div>
					<div className="mt-2 flex h-full items-center justify-end whitespace-nowrap">
						<div className="flex-1 pr-2 pl-5">
							<p className="text-gray-500 text-xl dark:text-gray-400">
								<button
									type="button"
									onClick={() => navigateWithTransition(router, "/pages")}
									className="flex cursor-pointer items-center">
									<span className="mr-2 h-6 w-6">
										<Icon name="left" />
									</span>
									Pages
								</button>
							</p>
						</div>
					</div>
				</div>
			</section>
			<div className="my-5">
				<hr className="dark:border-gray-600" />
			</div>
			<section className="mb-10">
				<label className="inline-flex items-center rounded-tl-xl rounded-tr-xl border border-gray-300 bg-white px-4 pt-[4px] pb-1 font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-5 w-5 text-blue-500">
						<Icon name="pencilTool" />
					</span>
					<span className="uppercase">Tools</span>
				</label>
				<div className="my-2 mb-4 flex w-full items-center rounded-br-xl rounded-bl-xl border border-gray-300 bg-white px-4 py-3 shadow-xs dark:border-gray-600 dark:bg-gray-700">
					<p className="items-center text-neutral-500 text-xl tracking-wide dark:text-gray-300">
						With a high bar for build quality, aesthetic, and usability. Here
						are some of the indie tools that I use or researched.
					</p>
				</div>
				<div className="mt-5 grid grid-cols-2 gap-4" data-cy="toolsItems">
					<div className="hover:-translate-y-0.5 z-40 flex w-full cursor-pointer flex-col rounded-md border bg-white shadow-xs transition-all duration-300 hover:border-gray-300 hover:shadow-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600 dark:hover:shadow-none">
						<a
							href="https://beyz.ai/meeting-assistant"
							target="_blank"
							rel="noreferrer"
							className="w-full flex-1 items-center px-6 py-4">
							<h1 className="mb-0.5 flex items-center font-medium text-2xl tracking-wide">
								<Image
									alt="Notion"
									src="https://static.ouorz.com/beyz_logo.jpeg"
									width={20}
									height={20}
									className="rounded-full border border-gray-200 dark:border-gray-500"
									loading="lazy"
								/>
								<span className="ml-2">Beyz</span>
							</h1>
							<p
								className="overflow-hidden text-ellipsis whitespace-nowrap text-4 text-gray-500 tracking-wide dark:text-gray-400"
								dangerouslySetInnerHTML={{
									__html: trimStr("Real-Time Meeting Assistant", 150),
								}}
							/>
						</a>
					</div>
					<div className="hover:-translate-y-0.5 z-40 flex w-full cursor-pointer flex-col rounded-md border bg-white shadow-xs transition-all duration-300 hover:border-gray-300 hover:shadow-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600 dark:hover:shadow-none">
						<a
							href="https://cap.so"
							target="_blank"
							rel="noreferrer"
							className="w-full flex-1 items-center px-6 py-4">
							<h1 className="mb-0.5 flex items-center font-medium text-2xl tracking-wide">
								<Image
									alt="Notion"
									src="https://static.ouorz.com/cap-logo.png"
									width={20}
									height={20}
									className="rounded-full border border-gray-200 dark:border-gray-500"
									loading="lazy"
								/>
								<span className="ml-2">Cap</span>
							</h1>
							<p
								className="overflow-hidden text-ellipsis whitespace-nowrap text-4 text-gray-500 tracking-wide dark:text-gray-400"
								dangerouslySetInnerHTML={{
									__html: trimStr("Open-source screen recording tool", 150),
								}}
							/>
						</a>
					</div>
					<div className="hover:-translate-y-0.5 z-40 flex w-full cursor-pointer flex-col rounded-md border bg-white shadow-xs transition-all duration-300 hover:border-gray-300 hover:shadow-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600 dark:hover:shadow-none">
						<a
							href="https://www.mintlify.com"
							target="_blank"
							rel="noreferrer"
							className="w-full flex-1 items-center px-6 py-4">
							<h1 className="mb-0.5 flex items-center font-medium text-2xl tracking-wide">
								<Image
									alt="Notion"
									src="https://static.ouorz.com/mintlify_logo.png"
									width={20}
									height={20}
									className="rounded-full border border-gray-200 dark:border-gray-500"
									loading="lazy"
								/>
								<span className="ml-2">Mintlify</span>
							</h1>
							<p
								className="overflow-hidden text-ellipsis whitespace-nowrap text-4 text-gray-500 tracking-wide dark:text-gray-400"
								dangerouslySetInnerHTML={{
									__html: trimStr(
										"The intelligent documentation platform",
										150
									),
								}}
							/>
						</a>
					</div>
					<div className="hover:-translate-y-0.5 z-40 flex w-full cursor-pointer flex-col rounded-md border bg-white shadow-xs transition-all duration-300 hover:border-gray-300 hover:shadow-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600 dark:hover:shadow-none">
						<a
							href="https://www.raycast.com"
							target="_blank"
							rel="noreferrer"
							className="w-full flex-1 items-center px-6 py-4">
							<h1 className="mb-0.5 flex items-center font-medium text-2xl tracking-wide">
								<Image
									alt="Notion"
									src="https://static.ouorz.com/raycast-logo.png"
									width={20}
									height={20}
									className="rounded-full border border-gray-200 dark:border-gray-500"
									loading="lazy"
								/>
								<span className="ml-2">Raycast</span>
							</h1>
							<p
								className="overflow-hidden text-ellipsis whitespace-nowrap text-4 text-gray-500 tracking-wide dark:text-gray-400"
								dangerouslySetInnerHTML={{
									__html: trimStr("macOS Spotlight alternative", 150),
								}}
							/>
						</a>
					</div>
				</div>
			</section>
			<div className="mb-10">
				<hr className="dark:border-gray-600" />
			</div>
			<section className="mb-10">
				<label className="inline-flex items-center rounded-tl-xl rounded-tr-xl border border-gray-300 bg-white px-4 pt-[4px] pb-1 font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-5 w-5 text-orange-500">
						<Icon name="people" />
					</span>
					<span className="uppercase">Webring</span>
				</label>
				<div className="my-2 mb-4 flex w-full items-center rounded-br-xl rounded-bl-xl border border-gray-300 bg-white px-4 py-3 shadow-xs dark:border-gray-600 dark:bg-gray-700">
					<p className="items-center text-neutral-500 text-xl tracking-wide dark:text-gray-300">
						To join this webring, email me at ABC_tony.hlp@hotmail.com (with the
						leading "ABC_" removed).
					</p>
				</div>
				<div className="mt-5 grid grid-cols-2 gap-4" data-cy="friendsItems">
					{friends.length > 0 ? (
						friends.map((item, index) => {
							return (
								<div
									className="hover:-translate-y-0.5 z-40 flex w-full cursor-pointer flex-col rounded-md border bg-white shadow-xs transition-all duration-300 hover:border-gray-300 hover:shadow-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600 dark:hover:shadow-none"
									key={index}>
									<a
										href={item.post_metas.link}
										target="_blank"
										rel="noreferrer"
										className="w-full flex-1 items-center px-6 py-4">
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
							)
						})
					) : (
						<div className="col-span-2 rounded-md border border-gray-200 bg-white px-6 py-8 text-center dark:border-gray-700 dark:bg-gray-800">
							<p className="text-gray-500 dark:text-gray-400">
								No webring links available at the moment.
							</p>
						</div>
					)}
				</div>
			</section>
		</div>
	)
}

Links.layout = pageLayout

export const getStaticProps: GetStaticProps = async () => {
	try {
		const resCount = await fetch(getAPI("internal", "postStats"))
		if (!resCount.ok) {
			return {
				revalidate: 60,
				props: {
					friends: [],
				},
			}
		}

		const dataCount = await resCount.json()
		const count: number = dataCount?.count ?? 0

		if (count === 0) {
			return {
				revalidate: 60,
				props: {
					friends: [],
				},
			}
		}

		const res = await fetch(
			getAPI("internal", "posts", {
				cate: 2,
				perPage: count,
			})
		)

		if (!res.ok) {
			return {
				revalidate: 60,
				props: {
					friends: [],
				},
			}
		}

		const data = await res.json()

		return {
			revalidate: 24 * 60 * 60,
			props: {
				friends: Array.isArray(data) ? data : [],
			},
		}
	} catch (error) {
		console.error("Failed to fetch friends data:", error)
		return {
			revalidate: 60,
			props: {
				friends: [],
			},
		}
	}
}

export default Links
