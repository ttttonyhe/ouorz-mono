import { Icon } from "@twilight-toolkit/ui"

interface Props {
	stopLoading: boolean
	setStopLoading: any
}

export default function CardClickable({ stopLoading, setStopLoading }: Props) {
	return (
		<div
			data-oa="click-loadingSwitch"
			className="mb-6 w-full cursor-pointer rounded-md border bg-white text-center shadow-xs hover:shadow-inner dark:border-gray-800 dark:bg-gray-800"
			onClick={() => {
				setStopLoading(!stopLoading)
			}}>
			{stopLoading ? (
				<p className="flex justify-center p-3.5 font-light text-gray-600 text-xl tracking-wide dark:text-gray-400">
					<span className="mt-0.5 mr-3 h-6 w-6">
						<Icon name="play" />
					</span>
					Resume Loading
				</p>
			) : (
				<p className="flex justify-center p-3.5 font-light text-gray-600 text-xl tracking-wide dark:text-gray-400">
					<span className="mt-0.5 mr-3 h-6 w-6">
						<Icon name="pause" />
					</span>
					Terminate Loading
				</p>
			)}
		</div>
	)
}
