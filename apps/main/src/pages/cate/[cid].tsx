import { Icon } from "@twilight-toolkit/ui"
import type { GetServerSideProps } from "next"
import Head from "next/head"
import Link from "next/link"
import { useRouter } from "next/router"
import { useEffect } from "react"
import List from "~/components/List"
import { pageLayout } from "~/components/Page"
import SubscriptionBox from "~/components/SubscriptionBox"
import type { NextPageWithLayout } from "~/pages/_app"
import getAPI from "~/utilities/api"

interface CateProps {
	info: { status: boolean; name: string; count: number; id: number }
}

const Cate: NextPageWithLayout = ({ info }: CateProps) => {
	const title = `${info.name} - Tony He`
	const router = useRouter()

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
				<div className="mt-0 pt-24 lg:mt-20 lg:pt-0">
					<div className="mb-4 items-center lg:flex">
						<div className="flex-1 items-center">
							<h1 className="flex justify-center font-medium text-1 text-black tracking-wide lg:justify-start dark:text-white">
								<span className="mr-3 inline-block cursor-pointer hover:animate-spin">
									🗂️
								</span>
								<span data-cy="cateName">{info.name}</span>
							</h1>
						</div>
						<div className="mt-2 flex h-full items-center justify-center whitespace-nowrap lg:justify-end">
							<div className="border-r border-r-gray-200 px-5 lg:flex-1 lg:text-center">
								<p className="flex items-center text-gray-500 text-xl dark:text-gray-400">
									<span className="mr-2 h-6 w-6">
										<Icon name="count" />
									</span>
									{info.count} posts
								</p>
							</div>
							<div className="px-5 lg:flex-1">
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
					<SubscriptionBox type="sm" />
				</div>
				<div className="mt-10 lg:mt-5">
					<List.Infinite type="cate" cate={info.id} />
				</div>
			</div>
		)
	} else {
		useEffect(() => {
			router.replace("/404")
		}, [router.replace])

		return (
			<div className="mx-auto w-1/3 animate-pulse rounded-md rounded-tl-none rounded-tr-none border border-t-0 bg-white py-3 text-center shadow-xs">
				<h1 className="font-medium text-lg">404 Not Found</h1>
				<p className="font-light text-gray-500 text-sm tracking-wide">
					redirecting...
				</p>
			</div>
		)
	}
}

Cate.layout = pageLayout

export const getServerSideProps: GetServerSideProps = async (context) => {
	const cid = context.params.cid

	const resInfo = await fetch(
		getAPI("internal", "category", {
			id: parseInt(cid as string, 10),
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
