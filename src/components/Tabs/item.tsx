import Link from 'next/link'
import Icon from '~/components/Icon'
import { TabItemComponentProps } from '.'

const TabItemComponent = (props: TabItemComponentProps) => {
	const { label, icon, link } = props

	const TabButton = () => (
		<button className="py-2 px-5 rounded-md cursor-pointer focus:outline-none justify-center items-center text-xl tracking-wider flex lg:flex">
			{icon && (
				<span className="w-6 h-6 mr-1 -mt-[1px]">
					<Icon name={icon} />
				</span>
			)}
			{label}
		</button>
	)

	if (link?.internal) {
		return (
			<Link href={link.internal}>
				<a className="flex items-center w-full h-full">
					<TabButton />
				</a>
			</Link>
		)
	}

	if (link?.external) {
		return (
			<a
				href={link.external}
				rel="noopener noreferrer"
				target="_blank"
				className="flex items-center"
			>
				<TabButton />
			</a>
		)
	}

	return <TabButton />
}

export default TabItemComponent
