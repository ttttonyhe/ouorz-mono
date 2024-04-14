import { createContext } from "react"

export const SidebarContext = createContext({
	activePathname: "",
})

export const SidebarProvider = SidebarContext.Provider

export default SidebarContext
