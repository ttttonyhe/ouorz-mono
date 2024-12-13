"use client"

import responsive from "@/styles/responsive.module.css"
import cn from "clsx"
import { usePathname } from "next/navigation"
import { FC } from "react"

interface AsideProps {
	horizontalShrink?: boolean
}

const Aside: FC<AsideProps> = ({ horizontalShrink = true }) => {
	const pathname = usePathname()
	return (
		<section
			className={cn(
				horizontalShrink && responsive["sidebar-width"],
				"relative z-sidebar flex h-full flex-col border-l bg-white-tinted dark:border-neutral-800 dark:bg-neutral-900"
			)}>
			<h1>Search bar</h1>
			<p>{pathname}</p>
			<ul>
				<li>Reading List</li>
				<li>Podcasts</li>
			</ul>
		</section>
	)
}

export default Aside
