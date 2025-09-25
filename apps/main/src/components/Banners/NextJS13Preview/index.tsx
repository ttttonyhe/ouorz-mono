import { Icon } from "@twilight-toolkit/ui"
import Image from "next/image"

const NextJS13Preview = () => {
	return (
		<div className="flex w-full gap-x-4 rounded-md border bg-white px-4 py-2.5 pl-4.5 shadow-xs dark:border-gray-800 dark:bg-gray-800">
			<div className="flex items-center">
				<Image
					src="https://static.ouorz.com/nextjs-icon-light-background.svg"
					width={30}
					height={30}
					alt="NextJS"
					className="dark:rounded-full dark:border"
					loading="eager"
				/>
			</div>
			<div className="flex flex-1 items-center justify-between">
				<div className="leading-5">
					<div className="flex gap-x-1 font-medium">
						<div>
							<p className="text-gray-700 dark:text-white">
								Preview Next.JS 13 Version
							</p>
						</div>
						<div className="-translate-y-[1.5px]">
							<label className="rounded-full bg-green-100 px-2 pt-px pb-[2px] text-[0.65rem] text-green-500 leading-[0.65rem] dark:bg-green-800 dark:text-green-300">
								Alpha
							</label>
						</div>
					</div>
					<p className="text-gray-400 text-xs">
						Powered by nested layouts, React server components, streaming SSR
						and more...
					</p>
				</div>
				<div>
					<a
						className="text-gray-500 dark:text-gray-300"
						href="https://preview.ouorz.com"
						target="_blank"
						rel="noreferrer"
						data-oa="click-nextjs13Ppreview">
						<button className="effect-pressing h-[1.8rem] w-[1.8rem] rounded-md p-2 hover:bg-gray-200 dark:hover:bg-gray-600">
							<Icon name="right" />
						</button>
					</a>
				</div>
			</div>
		</div>
	)
}

export default NextJS13Preview
