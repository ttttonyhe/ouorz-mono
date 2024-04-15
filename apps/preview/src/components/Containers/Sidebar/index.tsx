"use client"

import Footer from "../Footer"
import Header from "../Header"
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
		<section
			className={cn(
				horizontalShrink && responsive["sidebar-width"],
				"relative z-sidebar flex h-full flex-col border-r bg-white-tinted dark:border-neutral-800 dark:bg-neutral-900"
			)}>
			<Header />
			<nav className="flex h-full flex-col gap-y-2">
				<SidebarProvider value={{ activePathname: pathname }}>
					<SidebarSection title="View">
						<SidebarItem pathname="/">Home</SidebarItem>
						<SidebarItem pathname="/research">Research</SidebarItem>
						<SidebarItem pathname="/work">Work</SidebarItem>
					</SidebarSection>
					<SidebarSection title="Outputs">
						<SidebarItem pathname="/blog">Blog</SidebarItem>
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
			<Footer />
		</section>
	)
}

export default Sidebar
