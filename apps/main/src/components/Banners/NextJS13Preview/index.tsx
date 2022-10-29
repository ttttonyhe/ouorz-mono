import { Icon } from '@twilight-toolkit/ui'
import Image from 'next/image'

const NextJS13Preview = () => {
	return (
		<div className="bg-white px-4 py-2.5 rounded-md w-full flex gap-x-4 shadow-sm  dark:bg-gray-800 dark:border-gray-800 border">
			<div className="flex items-center">
				<Image
					src="https://static.ouorz.com/nextjs-icon-light-background.svg"
					width={30}
					height={30}
					alt="NextJS"
					className="dark:border dark:rounded-full"
				/>
			</div>
			<div className="flex flex-1 justify-between items-center">
				<div className="leading-5">
					<div className="flex gap-x-1 font-medium">
						<div>
							<p className="text-gray-700 dark:text-white">
								Preview Next.JS 13 Version
							</p>
						</div>
						<div className="-translate-y-[1.5px]">
							<label className="px-2 pt-[1px] pb-[2px] bg-blue-100 dark:bg-blue-800 text-[0.65rem] leading-[0.65rem] rounded-full text-blue-500 dark:text-blue-300">
								Alpha
							</label>
						</div>
					</div>
					<p className="text-gray-400 text-xs">
						Powered by nested layouts, React server components, Suspense
						streaming and more...
					</p>
				</div>
				<div>
					<a
						className="text-gray-500 dark:text-gray-300"
						href="https://preview.ouorz.com"
						target="_blank"
						rel="noreferrer"
					>
						<button className="w-[1.8rem] h-[1.8rem] hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md p-2">
							<Icon name="right" />
						</button>
					</a>
				</div>
			</div>
		</div>
	)
}

export default NextJS13Preview
