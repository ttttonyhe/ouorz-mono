import {
	createContext,
	useContext,
	type Dispatch,
	type SetStateAction,
} from "react"

export type KbarInputValueChangeHandler = (newValue: string) => void

export type KbarContext = {
	inputValue: string
	setInputValue: (value: string) => void
	inputValueChangeHandler?: KbarInputValueChangeHandler
	setInputValueChangeHandler: Dispatch<
		SetStateAction<KbarInputValueChangeHandler | undefined>
	>
}

const kbarContext = createContext<KbarContext | null>(null)
const KbarContextProvider = kbarContext.Provider

const useKbarContext = () => {
	const context = useContext(kbarContext)
	if (!context) throw new Error("KbarPanel requires KbarContextProvider")
	return context
}

export { useKbarContext, KbarContextProvider }
