export default function CardSkeleton() {
	return (
		<div
			aria-hidden="true"
			className="mb-6 w-full rounded-md border bg-white p-10 shadow-xs dark:border-gray-800 dark:bg-gray-800">
			<div className="flex gap-x-6">
				<div className="h-[100px] w-[31%] shrink-0 animate-pulse rounded-md bg-gray-100 dark:bg-neutral-600" />
				<div className="flex flex-1 flex-col gap-y-2.5">
					<div className="h-[30px] w-full animate-pulse rounded-md bg-gray-100 dark:bg-neutral-600" />
					<div className="h-[15px] w-11/12 animate-pulse rounded-xs bg-gray-100 dark:bg-neutral-600" />
					<div className="h-[15px] w-3/4 animate-pulse rounded-xs bg-gray-100 dark:bg-neutral-600" />
					<div className="h-[15px] w-5/6 animate-pulse rounded-xs bg-gray-100 dark:bg-neutral-600" />
				</div>
			</div>
		</div>
	)
}
