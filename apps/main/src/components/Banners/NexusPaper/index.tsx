import { Icon } from "@twilight-toolkit/ui"
import Link from "next/link"

const NexusPaper = () => {
	return (
		<div className="w-full rounded-lg border bg-white shadow-sm dark:border-gray-800 dark:bg-gray-800">
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
						className="effect-pressing flex items-center gap-x-[4px] rounded-md border px-3 py-1 font-serif shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-700 dark:hover:border-gray-600 dark:hover:bg-gray-600">
						<span className="h-4.5 w-4.5 lg:h-[16px] lg:w-[16px]">
							<Icon name="paper" />
						</span>
						<span>Our Paper</span>
					</a>
					<a
						href="https://github.com/Kevin-Zh-CS/NEXUS"
						rel="noreferrer noopener"
						className="effect-pressing flex items-center gap-x-[4px] rounded-md border px-3 py-1 font-serif shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-700 dark:hover:border-gray-600 dark:hover:bg-gray-600">
						<span className="h-4.5 w-4.5 lg:h-[16px] lg:w-[16px]">
							<Icon name="code" />
						</span>
						<span>Open-source Implementation</span>
					</a>
				</div>
			</div>
		</div>
	)
}

export default NexusPaper
