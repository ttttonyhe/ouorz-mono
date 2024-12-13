"use client"

import { SidebarProvider } from "@/components/Containers/Sidebar/context"
import { usePathname } from "next/navigation"
import { FC, PropsWithChildren } from "react"

const SidebarLayout: FC<PropsWithChildren> = ({ children }) => {
	const pathname = usePathname()
	return (
		<SidebarProvider value={{ activePathname: pathname }}>
			{children}
		</SidebarProvider>
	)
}

export default SidebarLayout
