import { Icon } from "@twilight-toolkit/ui"
import Link from "next/link"

const PagesAndLinks = () => {
	return (
		<div className="w-full rounded-md border bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
			<div className="flex w-full items-center justify-between gap-x-2.5 border-b border-gray-200 px-4.5 py-2.5 dark:border-gray-700">
				<div className="flex items-center gap-x-[7px] text-[15px] font-medium tracking-wide text-gray-700 dark:text-white">
					<span className="h-4.5 w-4.5 lg:h-7 lg:w-7">
						<Icon name="cube" />
					</span>
					<span>Pages & Links</span>
				</div>
				<div className="-translate-y-[1.5px]">
					<label className="rounded-full border-[1.5px] bg-gray-100 pb-[2.5px] pl-[7px] pr-[6.5px] pt-[1.5px] text-[0.675rem] font-medium leading-[0.675rem] text-gray-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400">
						Pinned
					</label>
				</div>
			</div>
			<div className="mask-x mt-4 flex items-center justify-between gap-x-2.5 overflow-x-auto whitespace-nowrap px-4.5 pb-4 text-sm text-gray-600 dark:text-gray-300">
				<div className="flex items-center gap-x-2.5">
					<Link
						href="https://cal.com/tonyhe/15min"
						target="_blank"
						className="effect-pressing flex items-center gap-x-[4px] rounded-md border px-3.5 py-1 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-700 dark:hover:border-gray-600 dark:hover:bg-gray-600">
						<span className="h-4.5 w-4.5 lg:h-[16px] lg:w-[16px]">
							<Icon name="calendarSchedule" />
						</span>
						<span>Schedule a Meeting</span>
					</Link>
					<Link
						href="https://static.ouorz.com/tonyhe_cv_v2.pdf"
						target="_blank"
						className="effect-pressing flex items-center gap-x-[4px] rounded-md border px-3 py-1 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-700 dark:hover:border-gray-600 dark:hover:bg-gray-600">
						<span className="h-4.5 w-4.5 lg:h-[16px] lg:w-[16px]">
							<Icon name="profile" />
						</span>
						<span className="flex items-center gap-x-2">
							<span>Curriculum Vitae</span>
							{/* <span className="text-xs tracking-tight text-gray-400">
								(May not be up-to-date)
							</span> */}
						</span>
					</Link>
					<Link
						href="https://forms.gle/iXg7X7nS1Z8PvWPFA"
						target="_blank"
						className="effect-pressing flex items-center gap-x-[4px] rounded-md border px-3 py-1 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-700 dark:hover:border-gray-600 dark:hover:bg-gray-600">
						<span className="h-4.5 w-4.5 lg:h-[16px] lg:w-[16px]">
							<Icon name="comments" />
						</span>
						<span className="flex items-center gap-x-2">
							<span>Feedback</span>
						</span>
					</Link>
					{/* <Link
						href="https://github.com/ttttonyhe"
						target="_blank"
						className="effect-pressing flex items-center gap-x-[4px] rounded-md border px-3 py-1 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-700 dark:hover:border-gray-600 dark:hover:bg-gray-600">
						<span className="h-4.5 w-4.5 lg:h-[16px] lg:w-[16px]">
							<Icon name="twitterX" />
						</span>
						<span>Twitter</span>
					</Link> */}
				</div>
				<div>
					<Link
						href="/pages"
						aria-label="See all pages"
						className="group flex h-[25px] w-[25px] items-center justify-center overflow-hidden rounded-full bg-gray-100 text-gray-500 transition-all ease-in-out hover:w-[76px] dark:border dark:border-gray-600 dark:bg-transparent dark:text-gray-500">
						<span className="effect-pressing more-to-come absolute right-0 flex w-[76px] justify-end gap-x-[4px] pr-[4px] leading-none transition-all ease-in-out dark:pr-1">
							<span className="-mt-[0.5px] hidden text-xs opacity-0 transition-all delay-100 ease-in-out group-hover:block group-hover:opacity-100">
								See all
							</span>
							<span className="h-[16px] w-[16px] transition-all ease-in-out">
								<Icon name="chevronRight" />
							</span>
						</span>
					</Link>
				</div>
			</div>
		</div>
	)
}

export default PagesAndLinks
