"use client"

import responsive from "@/styles/responsive.module.css"
import cn from "clsx"
import { FC } from "react"

interface PanelProps {
	horizontalShrink?: boolean
}

const Panel: FC<PanelProps> = ({ horizontalShrink = true }) => {
	return (
		<section
			className={cn(
				horizontalShrink && responsive["panel-width"],
				"relative z-panel flex h-full flex-col border-r bg-white-tinted dark:border-neutral-800 dark:bg-neutral-900"
			)}>
			<h1>Panel</h1>
			<ul>
				<li>Reading List</li>
				<li>Podcasts</li>
			</ul>
		</section>
	)
}

export default Panel
