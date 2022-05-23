import { createContext } from 'react'

export type KbarContext = {
	inputValue: string
	setInputValue: (value: string) => void
}

const kbarContext = createContext<KbarContext>(null)
const KbarContextProvider = kbarContext.Provider

export { kbarContext, KbarContextProvider }
