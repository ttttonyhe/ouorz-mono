import { Icon } from "@twilight-toolkit/ui"
import Link from "next/link"

import type { TabItemComponentProps } from "."

interface TabButtonProps {
	label: TabItemComponentProps["label"]
	icon: TabItemComponentProps["icon"]
	linked: boolean
}

const TabButton = ({ label, icon, linked }: TabButtonProps) => {
	const Component = linked ? "span" : "button"
	return (
		<Component
			type={linked ? undefined : "button"}
			className="flex cursor-pointer items-center justify-center rounded-md px-5 py-2 text-xl tracking-wider focus:outline-hidden lg:flex">
			{icon && (
				<span className="mr-1 h-6 w-6">
					<Icon name={icon} />
				</span>
			)}
			{label}
		</Component>
	)
}

const TabItemComponent = (props: TabItemComponentProps) => {
	const { label, icon, link } = props
	const button = (
		<TabButton
			label={label}
			icon={icon}
			linked={!!(link?.internal || link?.external)}
		/>
	)

	if (link?.internal) {
		return (
			<Link href={link.internal} className="flex h-full w-full items-center">
				{button}
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
				{button}
			</a>
		)
	}

	return button
}

export default TabItemComponent
