import { Icon } from "@twilight-toolkit/ui"
import dynamic from "next/dynamic"

const NexmentDiv = dynamic(() => import("~/components/Nexment"), {
	ssr: false,
})

export default function CommentBox() {
	return (
		<div className="bg-white p-5 dark:border-gray-800 dark:bg-gray-800 lg:mt-5 lg:rounded-xl lg:border lg:px-20 lg:py-11 lg:shadow-xs">
			<div className="mb-8">
				<h1 className="flex items-center text-3xl font-medium tracking-wide text-gray-700 dark:text-white!">
					<span className="mr-2 h-9 w-9">
						<Icon name="comments" />
					</span>
					Comments
				</h1>
				<p className="mb-5 mt-1 pl-1 text-xl tracking-wide text-gray-500 dark:text-gray-400">
					Leave a comment to join the discussion
				</p>
			</div>
			<NexmentDiv />
		</div>
	)
}
