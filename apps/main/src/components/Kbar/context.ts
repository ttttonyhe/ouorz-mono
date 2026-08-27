import { createContext, type Dispatch, type SetStateAction } from "react"

export type KbarInputValueChangeHandler = (newValue: string) => void

export type KbarContext = {
	inputValue: string
	setInputValue: (value: string) => void
	inputValueChangeHandler?: KbarInputValueChangeHandler
	setInputValueChangeHandler?: Dispatch<
		SetStateAction<KbarInputValueChangeHandler | undefined>
	>
}

const kbarContext = createContext<KbarContext>({
	inputValue: "",
	setInputValue: () => {},
})
const KbarContextProvider = kbarContext.Provider

export { kbarContext, KbarContextProvider }
