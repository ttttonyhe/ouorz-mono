'use client'

import Link from 'next/link'
import { Icon } from '@twilight-toolkit/ui'
import { usePathname } from 'next/navigation'
import Headings from '~/constants/headings'

const PageDefaultLayout = ({ children }: LayoutProps) => {
	const pathname = usePathname()
	const headingData = Headings[pathname]

	return (
		<>
			<div className="lg:mt-20 mt-0 lg:pt-0 pt-24">
				<div className="mb-4 flex items-center">
					<div className="flex-1 items-center">
						<h1 className="font-medium text-1 text-black dark:text-white tracking-wide">
							<span className="hover:animate-spin inline-block cursor-pointer mr-3">
								{headingData.icon}
							</span>
							{headingData.title}
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
				{headingData.note && (
					<div className="border shadow-sm w-full py-3 px-5 flex rounded-md bg-white dark:bg-gray-800 dark:border-gray-800 items-center my-2">
						<p className="text-xl tracking-wide text-gray-500 dark:text-gray-400 items-center">
							{headingData.note}
						</p>
					</div>
				)}
			</div>
			<>{children}</>
		</>
	)
}

export default PageDefaultLayout
