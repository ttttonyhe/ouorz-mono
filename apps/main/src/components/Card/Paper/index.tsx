import Link from "next/link"

type Link = {
	label: string
	href: string
	default?: boolean
}

type Venue = {
	name: string
	href: string
	color?: string
}

interface PaperCardProps {
	title: string
	authors: string | string[]
	venue: Venue
	links: Link[]
	accepted: boolean
}

const PaperCard = (props: PaperCardProps) => {
	const { title, authors, venue, links, accepted } = props

	// Find the default link
	const defaultLink = (links.find((link) => link.default) ?? venue).href

	return (
		<div
			onClick={() => {
				window?.open(defaultLink, "_blank")
			}}
			className="group flex w-full cursor-pointer flex-col gap-y-2 rounded-md border bg-white shadow-sm transition-all hover:-translate-y-1 hover:border-gray-300 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600">
			<div className="text-normal flex w-full items-center border-b border-gray-200 px-4.5 py-2.5 font-serif font-medium tracking-wide text-gray-700 dark:border-gray-700 dark:text-white dark:group-hover:border-gray-600">
				<p>{title}</p>
			</div>
			<div className="flex flex-col gap-y-2.5 px-4.5 pb-3.5 pt-1">
				<div className="text-sm tracking-wide text-gray-600 dark:text-gray-300">
					<p>{authors}</p>
				</div>
				<div className="flex flex-col items-start gap-x-2.5 gap-y-2 text-xs font-medium text-gray-500 lg:-ml-1 lg:flex-row lg:items-center">
					<div
						className={`rounded-full border bg-gray-100 px-2.5 py-0.5 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 ${venue.color || ""}`}>
						<Link href={venue.href}>
							{!accepted && "Under Submission to "}
							{venue.name}
						</Link>
					</div>
					<div className="flex items-center justify-between gap-x-2.5 overflow-x-auto whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
						{links.map((link) => (
							<Link
								key={link.href}
								href={link.href}
								onClick={(e) => e.stopPropagation()}
								className="underline underline-offset-4 transition-colors hover:text-green-600">
								<span>{link.label} →</span>
							</Link>
						))}
					</div>
				</div>
			</div>
		</div>
	)
}

export default PaperCard
