interface ServiceCardProps {
	serviceRole: string
	serviceType: string
	serviceTitle: string
	serviceOrganization?: string
}

const ServiceCard = (props: ServiceCardProps) => {
	const { serviceRole, serviceType, serviceTitle, serviceOrganization } = props

	return (
		<div className="flex w-full flex-col gap-y-2 rounded-md border bg-white shadow-xs lg:min-w-110 dark:border-gray-700 dark:bg-gray-800">
			<div className="flex w-full items-center justify-between gap-x-2.5 overflow-hidden overflow-x-auto whitespace-nowrap border-gray-200 border-b px-4.5 py-2.5 font-medium text-gray-700 text-normal tracking-wide dark:border-gray-700 dark:text-white">
				<div className="flex items-center gap-x-2">
					<p>{serviceRole}</p>
				</div>
				<label className="rounded-full border bg-gray-100 px-2.5 py-0.5 text-gray-500 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400">
					{serviceType}
				</label>
			</div>
			<div className="flex items-center justify-between gap-x-2.5 overflow-hidden overflow-x-auto whitespace-nowrap px-4.5 pt-1 pb-2.5">
				<div className="text-gray-600 text-sm tracking-wide dark:text-gray-300">
					<p>{serviceTitle}</p>
				</div>
			</div>
			{serviceOrganization && (
				<div className="-mt-[8px] flex items-center justify-between border-gray-200 border-t px-4.5 py-1.5 dark:border-gray-700">
					<p className="text-gray-500 text-sm dark:text-gray-400">
						{serviceOrganization}
					</p>
				</div>
			)}
		</div>
	)
}

export default ServiceCard
