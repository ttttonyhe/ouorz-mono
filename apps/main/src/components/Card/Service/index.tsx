interface ServiceCardProps {
	serviceRole: string
	serviceType: string
	serviceTitle: string
	serviceOrganization?: string
}

const ServiceCard = (props: ServiceCardProps) => {
	const { serviceRole, serviceType, serviceTitle, serviceOrganization } = props

	return (
		<div className="flex w-full flex-col gap-y-2 rounded-md border bg-white shadow-xs dark:border-gray-700 dark:bg-gray-800 lg:min-w-[27.5rem]">
			<div className="text-normal flex w-full items-center justify-between gap-x-2.5 overflow-hidden overflow-x-auto whitespace-nowrap border-b border-gray-200 px-4.5 py-2.5 font-medium tracking-wide text-gray-700 dark:border-gray-700 dark:text-white">
				<div className="flex items-center gap-x-2">
					<p>{serviceRole}</p>
				</div>
				<label className="rounded-full border bg-gray-100 px-2.5 py-0.5 text-xs text-gray-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400">
					{serviceType}
				</label>
			</div>
			<div className="flex items-center justify-between gap-x-2.5 overflow-hidden overflow-x-auto whitespace-nowrap px-4.5 pb-2.5 pt-1">
				<div className="text-sm tracking-wide text-gray-600 dark:text-gray-300">
					<p>{serviceTitle}</p>
				</div>
			</div>
			{serviceOrganization && (
				<div className="-mt-[8px] flex items-center justify-between border-t border-gray-200 px-4.5 py-1.5 dark:border-gray-700">
					<p className="text-sm text-gray-500 dark:text-gray-400">
						{serviceOrganization}
					</p>
				</div>
			)}
		</div>
	)
}

export default ServiceCard
