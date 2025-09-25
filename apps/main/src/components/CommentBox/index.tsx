import { Icon } from "@twilight-toolkit/ui"
import dynamic from "next/dynamic"

const NexmentDiv = dynamic(() => import("~/components/Nexment"), {
	ssr: false,
})

export default function CommentBox() {
	return (
		<div className="bg-white p-5 lg:mt-5 lg:rounded-xl lg:border lg:px-20 lg:py-11 lg:shadow-xs dark:border-gray-800 dark:bg-gray-800">
			<div className="mb-8">
				<h1 className="flex items-center font-medium text-3xl text-gray-700 tracking-wide dark:text-white!">
					<span className="mr-2 h-9 w-9">
						<Icon name="comments" />
					</span>
					Comments
				</h1>
				<p className="mt-1 mb-5 pl-1 text-gray-500 text-xl tracking-wide dark:text-gray-400">
					Leave a comment to join the discussion
				</p>
			</div>
			<NexmentDiv />
		</div>
	)
}
