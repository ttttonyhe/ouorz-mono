import { createContext } from 'react'
import { KbarListItem } from '.'

export type KbarContext = {
	list: KbarListItem[]
	keyBinding: string[]
	loading: boolean
	setLoading: (loading: boolean) => void
	placeholder: string
	inputValue: string
	setInputValue: (value: string) => void
	setDisplay: (display: boolean) => void
}

const kbarContext = createContext<KbarContext>(null)
const KbarContextProvider = kbarContext.Provider

export { kbarContext, KbarContextProvider }
