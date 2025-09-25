import { Icon } from "@twilight-toolkit/ui"
import { GlowingBackground } from "~/components/Visual"
import openLink from "~/utilities/externalLink"

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
	const valueLoaded = value && value !== "NaN"
	const denominatorLoaded = denominator && denominator !== "NaN"

	return (
		<div
			onClick={() => openLink(link)}
			className="glowing-div group flex cursor-pointer items-center overflow-hidden rounded-xl border bg-white px-7 py-5 shadow-xs dark:border-gray-800 dark:bg-gray-800">
			<div className="absolute top-0 left-0 flex h-full w-full items-start justify-end blur-3xl">
				<div
					className="-mt-8 h-1/6 w-full rounded-full transition-all duration-300 group-hover:h-2/6 group-hover:w-5/6"
					style={{
						backgroundColor: colorHex,
					}}
				/>
			</div>
			<GlowingBackground rounded="xl" />
			<div className="glowing-div-content">
				<figure
					className="mb-9 h-[2.5rem] w-[2.5rem]"
					style={{
						color: colorHex,
					}}>
					<Icon name={icon} />
				</figure>
				<h1
					className={`-mb-0.5 flex items-center font-bold text-[1.875rem] tracking-wide ${
						!valueLoaded && "animate-pulse"
					}`}>
					<span>
						{valueLoaded ? value : "- - -"}
						{denominatorLoaded && `/${denominator}`}
					</span>
				</h1>
				<p className="flex items-center overflow-hidden text-ellipsis whitespace-nowrap font-medium text-gray-700 tracking-wide text-opacity-70 dark:text-gray-400">
					<span>{description}</span>
					<span className="mt-0.5 ml-0 h-4.5 w-4.5 opacity-0 transition-all group-hover:ml-2 group-hover:opacity-100">
						<Icon name="right" />
					</span>
				</p>
			</div>
		</div>
	)
}
