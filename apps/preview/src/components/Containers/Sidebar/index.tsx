"use client"

import SidebarItem from "./SidebarItem"
import SidebarSection from "./SidebarSection"
import { SidebarProvider } from "./context"
import responsive from "@/styles/responsive.module.css"
import cn from "clsx"
import { usePathname } from "next/navigation"
import { FC } from "react"
import { useUpdateEffect } from "react-use"

interface SidebarProps {
	horizontalShrink?: boolean
	navigationCallback?: () => void
}

const Sidebar: FC<SidebarProps> = ({
	horizontalShrink = true,
	navigationCallback,
}) => {
	const pathname = usePathname()

	useUpdateEffect(() => {
		navigationCallback && navigationCallback()
	}, [pathname])

	return (
		<nav
			className={cn(
				horizontalShrink && responsive["sidebar-width"],
				"relative z-sidebar flex h-full flex-shrink-0 flex-col gap-y-2 bg-purple-500"
			)}>
			<SidebarProvider value={{ activePathname: pathname }}>
				<SidebarSection title="View">
					<SidebarItem pathname="/">Home</SidebarItem>
					<SidebarItem pathname="/research">Research</SidebarItem>
					<SidebarItem pathname="/work">Work</SidebarItem>
				</SidebarSection>
				<SidebarSection title="Outputs">
					<SidebarItem pathname="/posts">Posts</SidebarItem>
					<SidebarItem pathname="/projects">Projects</SidebarItem>
				</SidebarSection>
				<section>
					<h1>Inputs</h1>
					<ul>
						<li>Reading List</li>
						<li>Podcasts</li>
					</ul>
				</section>
			</SidebarProvider>
		</nav>
	)
}

export default Sidebar
