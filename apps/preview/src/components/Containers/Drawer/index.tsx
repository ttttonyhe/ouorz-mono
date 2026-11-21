"use client"

import { BREAKPOINTS, DRAWER_SNAP_POINTS } from "@/constants/ui"
import cn from "clsx"
import { useState, useSyncExternalStore } from "react"
import { Drawer as VaulDrawer } from "vaul"

import { Menu } from "../Sidebar"

import drawer from "@/styles/drawer.module.css"

const subscribeResize = (callback: () => void) => {
	window.addEventListener("resize", callback)
	return () => {
		window.removeEventListener("resize", callback)
	}
}

const getWindowWidth = () => window.innerWidth
const getServerWidth = () => BREAKPOINTS.LG

const Drawer = () => {
	const width = useSyncExternalStore(
		subscribeResize,
		getWindowWidth,
		getServerWidth
	)
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
						"fixed right-0 bottom-0 left-0 z-overlay mt-24 flex h-[96%] flex-col rounded-t-[10px] bg-zinc-100 outline-hidden lg:hidden"
					)}>
					<VaulDrawer.Title className="DialogTitle" />
					<div className="mx-auto mt-4 mb-4 h-1.5 w-12 shrink-0 rounded-full bg-zinc-300" />
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
