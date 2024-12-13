"use client"

import Footer from "../../Footer"
import Header from "../../Header"
import MenuContext from "../context"
import MenuItem from "./MenuItem"
import MenuSection from "./MenuSection"
import responsive from "@/styles/responsive.module.css"
import cn from "clsx"
import { FC, useContext } from "react"
import { useUpdateEffect } from "react-use"

interface MenuProps {
	horizontalShrink?: boolean
	navigationCallback?: () => void
}

const Menu: FC<MenuProps> = ({
	horizontalShrink = true,
	navigationCallback,
}) => {
	const { activePathname } = useContext(MenuContext)

	useUpdateEffect(() => {
		navigationCallback && navigationCallback()
	}, [activePathname])

	return (
		<section
			className={cn(
				horizontalShrink && responsive["sidebar-width"],
				"relative z-sidebar flex h-full flex-col border-r bg-white-tinted dark:border-neutral-800 dark:bg-neutral-900"
			)}>
			<Header />
			<p>Current: {activePathname}</p>
			<nav className="flex h-full flex-col gap-y-2">
				<MenuSection title="View">
					<MenuItem pathname="/">Home</MenuItem>
					<MenuItem pathname="/research">Research</MenuItem>
					<MenuItem pathname="/work">Work</MenuItem>
				</MenuSection>
				<MenuSection title="Outputs">
					<MenuItem pathname="/blog">Blog</MenuItem>
					<MenuItem pathname="/projects">Projects</MenuItem>
				</MenuSection>
				<section>
					<h1>Inputs</h1>
					<ul>
						<li>Reading List</li>
						<li>Podcasts</li>
					</ul>
				</section>
			</nav>
			<Footer />
		</section>
	)
}

export default Menu
