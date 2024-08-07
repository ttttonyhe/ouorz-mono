import { Icon } from "@twilight-toolkit/ui"

const NexusPaper = () => {
	return (
		<div className="w-full rounded-lg border bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
			<div className="flex w-full items-center justify-between gap-x-2.5 border-b border-gray-100 px-4.5 py-2.5 dark:border-gray-700">
				<div className="flex items-center gap-x-[7px] text-[15px] font-medium tracking-wide text-gray-700 dark:text-white">
					<span className="h-4.5 w-4.5 lg:h-7 lg:w-7">🧬</span>
					<span>NEXUS</span>
				</div>
				<div className="-translate-y-[1.5px]">
					<label className="rounded-full border-[1.5px] border-gray-700 pb-[2.5px] pl-[7px] pr-[6.5px] pt-[1.5px] text-[0.675rem] font-medium leading-[0.675rem] text-gray-700 dark:border-gray-600 dark:text-gray-400">
						Featured
					</label>
				</div>
			</div>
			<div className="mt-4 flex items-center px-4.5 pb-4 text-sm tracking-wider text-gray-600 dark:text-gray-300">
				<p>
					The first non-interactive protocol for secure transformer inference.
				</p>
			</div>
			<div className="mask-x flex items-center justify-between gap-x-2.5 overflow-x-auto whitespace-nowrap px-4.5 pb-4 text-sm text-gray-600 dark:text-gray-300">
				<div className="flex items-center gap-x-2.5">
					<a
						href="https://eprint.iacr.org/2024/136"
						rel="noreferrer noopener"
						target="_blank"
						className="effect-pressing flex items-center gap-x-[4px] rounded-md border px-3 py-1 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-700 dark:hover:border-gray-600 dark:hover:bg-gray-600">
						<span className="h-4.5 w-4.5 lg:h-[16px] lg:w-[16px]">
							<Icon name="paper" />
						</span>
						<span>
							Our Paper{" "}
							<span className="text-xs text-gray-400">(under submission)</span>
						</span>
					</a>
					<a
						href="https://github.com/Kevin-Zh-CS/NEXUS"
						rel="noreferrer noopener"
						target="_blank"
						className="effect-pressing flex items-center gap-x-[4px] rounded-md border px-3 py-1 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-700 dark:hover:border-gray-600 dark:hover:bg-gray-600">
						<span className="h-4.5 w-4.5 lg:h-[16px] lg:w-[16px]">
							<Icon name="code" />
						</span>
						<span>Open-source Implementation</span>
					</a>
				</div>
				<div>
					<a
						href="https://www.ndss-symposium.org"
						rel="noreferrer noopener"
						target="_blank"
						className="group flex h-[25px] w-[25px] items-center justify-center overflow-hidden rounded-full bg-gray-100 text-gray-500 transition-all ease-in-out hover:w-[105px] dark:border dark:border-gray-600 dark:bg-transparent dark:text-gray-500">
						<span className="effect-pressing more-to-come absolute right-0 flex w-[115px] justify-end gap-x-[4px] pr-[4px] leading-none transition-all ease-in-out dark:pr-1">
							<span className="-mt-[0.5px] hidden text-xs opacity-0 transition-all delay-100 ease-in-out group-hover:block group-hover:opacity-100">
								NDSS 2025
							</span>
							<span className="h-[16px] w-[16px] transition-all ease-in-out">
								<Icon name="chevronRight" />
							</span>
						</span>
					</a>
				</div>
			</div>
		</div>
	)
}

export default NexusPaper
