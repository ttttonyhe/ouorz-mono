"use client"

import Sidebar from "../Sidebar"
import { BREAKPOINTS } from "@/constants/ui"
import { useState } from "react"
import { useWindowSize } from "react-use"
import { Drawer as VaulDrawer } from "vaul"

const Drawer = () => {
	const { width } = useWindowSize()
	const [snap, setSnap] = useState<number | string | null>("148px")

	return (
		<VaulDrawer.Root
			open={width < BREAKPOINTS.LG}
			snapPoints={["148px", "355px", 1]}
			activeSnapPoint={snap}
			setActiveSnapPoint={setSnap}
			dismissible={false}>
			<VaulDrawer.Overlay className="fixed inset-0 z-header bg-black/40 lg:hidden" />
			<VaulDrawer.Portal>
				<VaulDrawer.Content className="z-overlay fixed bottom-0 left-0 right-0 mt-24 flex h-[96%] flex-col rounded-t-[10px] bg-zinc-100 lg:hidden">
					<div className="mx-auto mb-8 mt-3 h-1.5 w-12 flex-shrink-0 rounded-full bg-zinc-300" />
					<Sidebar shrink={false} />
				</VaulDrawer.Content>
				<VaulDrawer.Overlay />
			</VaulDrawer.Portal>
		</VaulDrawer.Root>
	)
}

export default Drawer
