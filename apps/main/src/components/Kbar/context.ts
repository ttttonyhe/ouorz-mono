import {
	createContext,
	useContext,
	type Dispatch,
	type SetStateAction,
} from "react"

export type KbarInputValueChangeHandler = (newValue: string) => void

export interface KbarContextValue {
	inputValue: string
	setInputValue: (value: string) => void
	inputValueChangeHandler: KbarInputValueChangeHandler | undefined
	setInputValueChangeHandler: Dispatch<
		SetStateAction<KbarInputValueChangeHandler | undefined>
	>
}

const KbarContext = createContext<KbarContextValue | null>(null)

const useKbarContext = () => {
	const context = useContext(KbarContext)
	if (!context) throw new Error("KbarPanel requires KbarContext")
	return context
}

export { KbarContext, useKbarContext }
