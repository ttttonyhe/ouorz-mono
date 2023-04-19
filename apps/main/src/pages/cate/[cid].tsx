import Head from "next/head"
import React from "react"
import { GetServerSideProps } from "next"
import { NextPageWithLayout } from "~/pages/_app"
import { pageLayout } from "~/components/Page"
import List from "~/components/List"
import getApi from "~/utilities/api"
import SubscriptionBox from "~/components/SubscriptionBox"
import { Icon } from "@twilight-toolkit/ui"
import Link from "next/link"
import redirect from "nextjs-redirect"

interface CateProps {
	info: { status: boolean; name: string; count: number; id: number }
}

const Redirect = redirect("/404")

const Cate: NextPageWithLayout = ({ info }: CateProps) => {
	const title = `${info.name} - TonyHe`

	if (info.status) {
		return (
			<div>
				<Head>
					<title>{title}</title>
					<link
						rel="icon"
						href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🗂️</text></svg>"
					/>
					<meta
						name="description"
						content={`TonyHe's content under category "${info.name}"`}
					/>
				</Head>
				<div className="lg:mt-20 mt-0 lg:pt-0 pt-24">
					<div className="mb-4 lg:flex items-center">
						<div className="flex-1 items-center">
							<h1 className="font-medium text-1 text-black dark:text-white tracking-wide flex justify-center lg:justify-start">
								<span className="hover:animate-spin inline-block cursor-pointer mr-3">
									🗂️
								</span>
								<span data-cy="cateName">{info.name}</span>
							</h1>
						</div>
						<div className="h-full flex lg:justify-end justify-center whitespace-nowrap items-center mt-2">
							<div className="border-r border-r-gray-200 lg:text-center lg:flex-1 px-5">
								<p className="text-xl text-gray-500 dark:text-gray-400 flex items-center">
									<span className="w-6 h-6 mr-2">
										<Icon name="count" />
									</span>
									{info.count} posts
								</p>
							</div>
							<div className="lg:flex-1 px-5">
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
					<SubscriptionBox type="sm" />
				</div>
				<div className="lg:mt-5 mt-10">
					<List.Infinite type="cate" cate={info.id} />
				</div>
			</div>
		)
	} else {
		return (
			<Redirect>
				<div className="text-center shadow-sm border rounded-md rounded-tl-none rounded-tr-none border-t-0 w-1/3 mx-auto bg-white py-3 animate-pulse">
					<h1 className="text-lg font-medium">404 Not Found</h1>
					<p className="text-gray-500 font-light tracking-wide text-sm">
						redirecting...
					</p>
				</div>
			</Redirect>
		)
	}
}

Cate.layout = pageLayout

export const getServerSideProps: GetServerSideProps = async (context) => {
	const cid = context.params.cid

	const resInfo = await fetch(
		getApi({
			cate: `${cid}`,
			getCate: true,
		})
	)

	if (!resInfo.ok) {
		return {
			props: {
				info: {
					status: false,
				},
			},
		}
	} else {
		const infoData = await resInfo.json()
		return {
			props: {
				info: {
					status: true,
					name: infoData.name,
					count: infoData.count,
					id: infoData.id,
				},
			},
		}
	}
}

export default Cate
