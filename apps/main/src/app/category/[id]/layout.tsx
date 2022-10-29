import { ReactNode } from 'react'
import Link from 'next/link'
import { Icon } from '@twilight-toolkit/ui'
import Page from '~/components/Page'
import SubscriptionBox from '~/components/SubscriptionBox'
import { CategoryProps, getCateData } from './page'

interface CategoryLayoutProps extends CategoryProps {
	children: ReactNode
}

const CategoryLayout = async ({ params, children }: CategoryLayoutProps) => {
	const cate = await getCateData(params.id)

	return (
		<Page>
			<div className="lg:mt-20 mt-0 lg:pt-0 pt-24">
				<div className="mb-4 lg:flex items-center">
					<div className="flex-1 items-center">
						<h1 className="font-medium text-1 text-black dark:text-white tracking-wide flex justify-center lg:justify-start">
							<span className="hover:animate-spin inline-block cursor-pointer mr-3">
								🗂️
							</span>
							<span data-cy="cateName">{cate.name}</span>
						</h1>
					</div>
					<div className="h-full flex lg:justify-end justify-center whitespace-nowrap items-center mt-2">
						<div className="border-r border-r-gray-200 lg:text-center lg:flex-1 px-5">
							<p className="text-xl text-gray-500 dark:text-gray-400 flex items-center">
								<span className="w-6 h-6 mr-2">
									<Icon name="count" />
								</span>
								{cate.count} posts
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
			<div className="lg:mt-5 mt-10">{children}</div>
		</Page>
	)
}

export default CategoryLayout
