import { createContext } from "react"

export type KbarContext = {
	inputValue: string
	setInputValue: (value: string) => void
	inputValueChangeHandler?: (newValue: string) => void
	setInputValueChangeHandler?: (handler: (newValue: string) => void) => void
}

const kbarContext = createContext<KbarContext>({
	inputValue: "",
	setInputValue: () => {},
})
const KbarContextProvider = kbarContext.Provider

export { kbarContext, KbarContextProvider }
