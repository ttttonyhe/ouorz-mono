import { GlowingBackground } from '~/components/Visual'
import { Icon } from '@twilight-toolkit/ui'
import openLink from '~/utilities/externalLink'

interface MetricCardProps {
	description: string
	value: string
	link: string
	icon: string
	colorHex: string
	denominator?: string
}

export default function MetricCard(props: MetricCardProps) {
	const { description, value, link, icon, colorHex, denominator } = props
	const valueLoaded = value && value !== 'NaN'
	const denominatorLoaded = denominator && denominator !== 'NaN'

	return (
		<div
			onClick={() => openLink(link)}
			className="group glowing-div overflow-hidden flex items-center border dark:bg-gray-800 dark:border-gray-800 rounded-xl shadow-sm py-5 px-7 bg-white cursor-pointer"
		>
			<div className="absolute flex items-start justify-end blur-3xl w-full h-full left-0 top-0">
				<div
					className="-mt-8 h-1/6 w-full rounded-full group-hover:h-2/6 group-hover:w-5/6 transition-all duration-300"
					style={{
						backgroundColor: colorHex,
					}}
				/>
			</div>
			<GlowingBackground rounded="xl" />
			<div className="glowing-div-content">
				<figure
					className="w-[2.5rem] h-[2.5rem] mb-9"
					style={{
						color: colorHex,
					}}
				>
					<Icon name={icon} />
				</figure>
				<h1
					className={`font-bold text-[1.875rem] tracking-wide flex items-center -mb-0.5 ${
						!valueLoaded && 'animate-pulse'
					}`}
				>
					<span>
						{valueLoaded ? value : '- - -'}
						{denominatorLoaded && '/' + denominator}
					</span>
				</h1>
				<p className="flex items-center text-gray-700 text-opacity-70 font-medium dark:text-gray-400 tracking-wide overflow-hidden text-ellipsis whitespace-nowrap">
					<span>{description}</span>
					<span className="w-4.5 h-4.5 mt-0.5 opacity-0 group-hover:opacity-100 ml-0 group-hover:ml-2 transition-all">
						<Icon name="right" />
					</span>
				</p>
			</div>
		</div>
	)
}
