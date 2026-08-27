"use client"

import { usePathname } from "next/navigation"
import { FC, PropsWithChildren } from "react"

import { SidebarProvider } from "@/components/Containers/Sidebar/context"

const SidebarLayout: FC<PropsWithChildren> = ({ children }) => {
	const pathname = usePathname()
	return (
		<SidebarProvider value={{ activePathname: pathname }}>
			{children}
		</SidebarProvider>
	)
}

export default SidebarLayout
