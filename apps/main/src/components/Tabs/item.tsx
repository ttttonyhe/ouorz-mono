import { Icon } from "@twilight-toolkit/ui"
import Link from "next/link"
import type { TabItemComponentProps } from "."

const TabItemComponent = (props: TabItemComponentProps) => {
	const { label, icon, link } = props

	const TabButton = () => (
		<button className="flex cursor-pointer items-center justify-center rounded-md px-5 py-2 text-xl tracking-wider focus:outline-hidden lg:flex">
			{icon && (
				<span className="mr-1 h-6 w-6">
					<Icon name={icon} />
				</span>
			)}
			{label}
		</button>
	)

	if (link?.internal) {
		return (
			<Link href={link.internal} className="flex h-full w-full items-center">
				<TabButton />
			</Link>
		)
	}

	if (link?.external) {
		return (
			<a
				href={link.external}
				rel="noopener noreferrer"
				target="_blank"
				className="flex items-center">
				<TabButton />
			</a>
		)
	}

	return <TabButton />
}

export default TabItemComponent
