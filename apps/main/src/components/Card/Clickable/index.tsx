import { Icon } from "@twilight-toolkit/ui"

interface Props {
	stopLoading: boolean
	setStopLoading: any
}

export default function CardClickable({ stopLoading, setStopLoading }: Props) {
	return (
		<div
			data-oa="click-loadingSwitch"
			className="shadow-xs mb-6 w-full cursor-pointer rounded-md border bg-white text-center hover:shadow-inner dark:border-gray-800 dark:bg-gray-800"
			onClick={() => {
				setStopLoading(!stopLoading)
			}}>
			{stopLoading ? (
				<p className="flex justify-center p-3.5 text-xl font-light tracking-wide text-gray-600 dark:text-gray-400">
					<span className="mr-3 mt-0.5 h-6 w-6">
						<Icon name="play" />
					</span>
					Resume Loading
				</p>
			) : (
				<p className="flex justify-center p-3.5 text-xl font-light tracking-wide text-gray-600 dark:text-gray-400">
					<span className="mr-3 mt-0.5 h-6 w-6">
						<Icon name="pause" />
					</span>
					Terminate Loading
				</p>
			)}
		</div>
	)
}
