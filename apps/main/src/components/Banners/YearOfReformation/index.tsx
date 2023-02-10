import Link from 'next/link'
import { Icon } from '@twilight-toolkit/ui'

const YearOfReformation = () => {
	return (
		<div className="bg-white rounded-md w-full shadow-sm dark:bg-gray-800 dark:border-gray-800 border">
			<div className="flex items-center justify-between gap-x-2.5 py-2.5 px-4.5 w-full border-b border-gray-100 dark:border-gray-700">
				<div className="flex items-center gap-x-[7px] font-medium text-gray-700 dark:text-white text-[15px] tracking-wide">
					<span className="lg:w-7 lg:h-7 h-4.5 w-4.5">
						<Icon name="cube" />
					</span>
					<span>My Year of Reformation</span>
				</div>
				<div className="-translate-y-[1.5px]">
					<label className="pl-[7px] pr-[6.5px] pt-[1.5px] pb-[2.5px] font-medium border-[1.5px] border-gray-700 dark:border-gray-600 text-[0.675rem] leading-[0.675rem] rounded-full text-gray-700 dark:text-gray-400">
						2023
					</label>
				</div>
			</div>
			<div className="flex items-center justify-between gap-x-2.5 mt-4 px-4.5 pb-4 text-sm text-gray-600 dark:text-gray-300">
				<div className="flex gap-x-2.5 items-center">
					<Link
						href="/reading-list"
						className="flex items-center gap-x-[4px] rounded-md shadow-sm border dark:border-gray-700 dark:bg-gray-700 py-1 px-3 font-serif hover:bg-gray-50 dark:hover:bg-gray-600 dark:hover:border-gray-600"
					>
						<span className="lg:w-[16px] lg:h-[16px] h-4.5 w-4.5">
							<Icon name="bookOpen" />
						</span>
						<span>Reading List</span>
					</Link>
					<Link
						href="/podcasts"
						className="flex items-center gap-x-[4px] rounded-md shadow-sm border dark:border-gray-700 dark:bg-gray-700 py-1 px-3 font-serif hover:bg-gray-50 dark:hover:bg-gray-600 dark:hover:border-gray-600"
					>
						<span className="lg:w-[16px] lg:h-[16px] h-4.5 w-4.5">
							<Icon name="microphone" />
						</span>
						<span>Podcasts</span>
					</Link>
				</div>
				<div>
					<Link
						href="/post/978"
						className="flex justify-center items-center h-[25px] w-[25px] transition-all ease-in-out hover:w-[115px] rounded-full group overflow-hidden text-gray-500 dark:text-gray-500 bg-gray-100 dark:bg-transparent dark:border dark:border-gray-600"
					>
						<span className="leading-none -ml-[74.5px] group-hover:ml-0 transition-all ease-in-out group-hover:-translate-x-[4px]">
							<span className="group-hover:opacity-100 opacity-0 delay-100 transition-all ease-in-out text-xs">
								More to Come
							</span>
						</span>
						<span className="h-[16px] w-[16px] transition-all ease-in-out mr-[3px] group-hover:-mr-[14px]">
							<Icon name="chevronRight" />
						</span>
					</Link>
				</div>
			</div>
		</div>
	)
}

export default YearOfReformation
