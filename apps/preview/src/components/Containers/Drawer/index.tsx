"use client"

import { Menu } from "../Sidebar"
import { BREAKPOINTS, DRAWER_SNAP_POINTS } from "@/constants/ui"
import drawer from "@/styles/drawer.module.css"
import cn from "clsx"
import { useState } from "react"
import { useWindowSize } from "react-use"
import { Drawer as VaulDrawer } from "vaul"

const Drawer = () => {
	const { width } = useWindowSize()
	const [currentSnapPoint, setCurrentSnapPoint] = useState<
		string | number | null
	>(DRAWER_SNAP_POINTS.SM)

	return (
		<VaulDrawer.Root
			open={width < BREAKPOINTS.LG}
			snapPoints={[
				DRAWER_SNAP_POINTS.SM,
				DRAWER_SNAP_POINTS.MD,
				DRAWER_SNAP_POINTS.LG,
			]}
			activeSnapPoint={currentSnapPoint}
			setActiveSnapPoint={setCurrentSnapPoint}
			dismissible={false}
			modal={false}>
			<VaulDrawer.Portal>
				<VaulDrawer.Content
					className={cn(
						drawer.dialog,
						"fixed bottom-0 left-0 right-0 z-overlay mt-24 flex h-[96%] flex-col rounded-t-[10px] bg-zinc-100 outline-none lg:hidden"
					)}>
					<VaulDrawer.Title className="DialogTitle" />
					<div className="mx-auto mb-4 mt-4 h-1.5 w-12 flex-shrink-0 rounded-full bg-zinc-300" />
					<Menu
						horizontalShrink={false}
						navigationCallback={() =>
							setCurrentSnapPoint(DRAWER_SNAP_POINTS.SM)
						}
					/>
				</VaulDrawer.Content>
				<VaulDrawer.Overlay />
			</VaulDrawer.Portal>
		</VaulDrawer.Root>
	)
}

export default Drawer
