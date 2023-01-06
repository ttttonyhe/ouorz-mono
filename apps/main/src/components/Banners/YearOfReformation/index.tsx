import { Button } from '@twilight-toolkit/ui'

const YearOfReformation = () => {
	return (
		<div className="bg-white px-4.5 py-4.5 rounded-md w-full shadow-sm dark:bg-gray-800 dark:border-gray-800 border">
			<div className="flex gap-x-2.5">
				<div className="flex font-medium items-center justify-center bg-gray-50 dark:bg-transparent dark:border-gray-600 text-gray-600 px-2.5 pb-0.5 border rounded-md">
					2023
				</div>
				<div className="text-gray-700 text-lg font-semibold">
					My Year of Reformation
				</div>
			</div>
			<div className="flex items-center gap-x-2 mt-4">
				<Button icon="bookOpen">Reading List</Button>
				<Button icon="bookOpen" disabled>
					More to come...
				</Button>
			</div>
		</div>
	)
}

export default YearOfReformation
