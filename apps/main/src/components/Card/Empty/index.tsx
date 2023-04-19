import { Icon } from "@twilight-toolkit/ui"
export default function CardEmpty() {
	return (
		<div className="w-full shadow-sm bg-white dark:bg-gray-800 dark:border-gray-800 rounded-md border mb-6 text-center">
			<p className="text-xl tracking-wide text-gray-600 dark:text-gray-400 font-light p-5 flex justify-center items-center">
				<span className="w-6 h-6 mr-3">
					<Icon name="empty" />
				</span>
				You Have Reached The Bottom Line
			</p>
		</div>
	)
}
