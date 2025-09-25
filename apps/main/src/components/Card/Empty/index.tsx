import { Icon } from "@twilight-toolkit/ui"

export default function CardEmpty() {
	return (
		<div className="mb-6 w-full rounded-md border bg-white text-center shadow-xs dark:border-gray-800 dark:bg-gray-800">
			<p className="flex items-center justify-center p-5 font-light text-gray-600 text-xl tracking-wide dark:text-gray-400">
				<span className="mr-3 h-6 w-6">
					<Icon name="empty" />
				</span>
				You Have Reached The Bottom Line
			</p>
		</div>
	)
}
