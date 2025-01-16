import Image from "next/image"

interface EmploymentCardProps {
	organization: string
	organizationFullName?: string
	jobTitle: string
	jobType: string
	dateString: string
	orgLogoSrc?: string
	organizationLocation?: string
}

const EmploymentCard = (props: EmploymentCardProps) => {
	const {
		organization,
		jobTitle,
		dateString,
		jobType,
		orgLogoSrc,
		organizationFullName,
		organizationLocation,
	} = props

	return (
		<div className="flex w-full flex-col gap-y-2 rounded-md border bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 lg:min-w-[27.5rem]">
			<div className="text-normal flex w-full items-center justify-between gap-x-2.5 overflow-hidden overflow-x-auto whitespace-nowrap border-b border-gray-200 px-4.5 py-2.5 font-medium tracking-wide text-gray-700 dark:border-gray-700 dark:text-white">
				<div className="flex items-center gap-x-2">
					{orgLogoSrc && (
						<Image
							src={orgLogoSrc}
							height={24}
							width={24}
							alt={`${organization} logo`}
							className="rounded-full border bg-white dark:border-gray-700"
						/>
					)}
					<p>{organization}</p>
				</div>
				<label className="rounded-full border bg-gray-100 px-2.5 py-0.5 text-xs text-gray-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400">
					{jobType}
				</label>
			</div>
			<div className="flex items-center justify-between gap-x-2.5 overflow-hidden overflow-x-auto whitespace-nowrap px-4.5 pb-2.5 pt-1">
				<div className="text-sm tracking-wide text-gray-600 dark:text-gray-300">
					<p>{jobTitle}</p>
				</div>
				<div className="flex flex-col items-start gap-x-2.5 gap-y-2 text-xs font-medium text-gray-500 lg:items-center">
					{dateString}
				</div>
			</div>
			{organizationFullName && (
				<div className="-mt-[8px] flex items-center justify-between border-t border-gray-200 px-4.5 py-1.5 dark:border-gray-700">
					<p className="text-sm text-gray-500 dark:text-gray-400">
						{organizationFullName}
					</p>
					<p className="text-sm text-gray-500 dark:text-gray-400">
						{organizationLocation}
					</p>
				</div>
			)}
		</div>
	)
}

export default EmploymentCard
