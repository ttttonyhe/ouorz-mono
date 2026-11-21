import { useSyncExternalStore } from "react"
import { z } from "zod"
import type { KbarListItem } from "~/components/Kbar"
import type { WPPost } from "~/constants/propTypes"

export type KbarAnimation = "in" | "out" | "transition" | ""
export type ReaderAnimation = "in" | "out" | ""

export interface KbarState {
	animation: KbarAnimation
	visible: boolean
	list: KbarListItem[]
	lists: Record<string, KbarListItem[]>
	location: string[]
	loading: boolean
	placeholder: string
}

export interface ReaderState {
	animation: ReaderAnimation
	visible: boolean
	postData?: WPPost
}

export interface AppState {
	headerTitle: string
	kbar: KbarState
	reader: ReaderState
}

export const SEARCH_LOCATION = ["home", "search"] as const

const DEFAULT_PLACEHOLDER = "Type your command or search..."

const searchResponseSchema = z.object({
	hits: z.array(
		z.object({
			post_id: z.number().int().positive(),
			post_title: z.string(),
		})
	),
})

let state: AppState = {
	headerTitle: "",
	kbar: {
		animation: "",
		visible: false,
		list: [],
		lists: {},
		location: ["home"],
		loading: false,
		placeholder: DEFAULT_PLACEHOLDER,
	},
	reader: {
		animation: "",
		visible: false,
	},
}

const listeners = new Set<() => void>()
let kbarTimer: ReturnType<typeof setTimeout> | null = null
let readerTimer: ReturnType<typeof setTimeout> | null = null
let searchController: AbortController | null = null

const emit = (next: AppState) => {
	state = next
	for (const listener of listeners) listener()
}

const subscribe = (listener: () => void) => {
	listeners.add(listener)
	return () => {
		listeners.delete(listener)
	}
}

const getSnapshot = () => state

const clearKbarTimer = () => {
	if (kbarTimer !== null) {
		clearTimeout(kbarTimer)
		kbarTimer = null
	}
}

const clearReaderTimer = () => {
	if (readerTimer !== null) {
		clearTimeout(readerTimer)
		readerTimer = null
	}
}

const abortSearch = () => {
	searchController?.abort()
	searchController = null
}

const updateKbarState = (patch: Partial<KbarState>) => {
	emit({ ...state, kbar: { ...state.kbar, ...patch } })
}

export const useAppState = <T>(selector: (snapshot: AppState) => T): T =>
	useSyncExternalStore(
		subscribe,
		() => selector(getSnapshot()),
		() => selector(getSnapshot())
	)

export const setHeaderTitle = (headerTitle: string) => {
	if (state.headerTitle === headerTitle) return
	emit({ ...state, headerTitle })
}

export const openReader = (postData: WPPost) => {
	clearReaderTimer()
	emit({
		...state,
		reader: { animation: "in", visible: true, postData },
	})
}

export const closeReader = () => {
	if (state.reader.animation === "out") return
	clearReaderTimer()
	emit({ ...state, reader: { ...state.reader, animation: "out" } })
	readerTimer = setTimeout(() => {
		readerTimer = null
		emit({ ...state, reader: { animation: "", visible: false } })
	}, 500)
}

export const activateKbar = (homeList: KbarListItem[]) => {
	clearKbarTimer()
	abortSearch()
	updateKbarState({
		lists: { ...state.kbar.lists, home: homeList },
		list: homeList,
		placeholder: DEFAULT_PLACEHOLDER,
		location: ["home"],
		animation: "in",
		loading: false,
		visible: true,
	})
}

export const deactivateKbar = () => {
	clearKbarTimer()
	abortSearch()
	updateKbarState({ animation: "out" })
	kbarTimer = setTimeout(() => {
		kbarTimer = null
		updateKbarState({ visible: false, animation: "" })
	}, 200)
}

export const navigateKbarToHome = () => {
	clearKbarTimer()
	abortSearch()
	updateKbarState({
		location: ["home"],
		placeholder: DEFAULT_PLACEHOLDER,
		list: state.kbar.lists.home ?? [],
		loading: false,
	})
}

export const navigateKbarToSearch = () => {
	clearKbarTimer()
	abortSearch()
	updateKbarState({
		location: [...SEARCH_LOCATION],
		loading: true,
		placeholder: "Search articles...",
	})
	kbarTimer = setTimeout(() => {
		kbarTimer = null
		updateKbarState({ loading: false })
	}, 500)
}

export const updateKbarView = (params: {
	key: string
	location: string[]
	items?: KbarListItem[]
	placeholder?: string
}) => {
	if (params.key === "home") {
		navigateKbarToHome()
		return
	}
	if (params.key === "search") {
		navigateKbarToSearch()
		return
	}

	clearKbarTimer()
	abortSearch()
	const lists = params.items
		? { ...state.kbar.lists, [params.key]: params.items }
		: state.kbar.lists

	updateKbarState({
		location: params.location,
		placeholder: params.placeholder ?? state.kbar.placeholder,
		lists,
		list: lists[params.key] ?? [],
		loading: false,
	})
}

export const searchKbarPosts = async (query: string) => {
	clearKbarTimer()
	abortSearch()
	const controller = new AbortController()
	searchController = controller
	updateKbarState({ loading: true })

	const cacheKey = `search-${query}`
	const cached = state.kbar.lists[cacheKey]
	if (cached) {
		kbarTimer = setTimeout(() => {
			kbarTimer = null
			if (controller.signal.aborted) return
			updateKbarState({ list: cached, loading: false })
		}, 100)
		return
	}

	try {
		const response = await fetch("/api/search", {
			method: "POST",
			headers: { "Content-Type": "application/json" },
			body: JSON.stringify({ query }),
			signal: controller.signal,
		})
		if (!response.ok) throw new Error(`Search failed: ${response.status}`)

		const data = searchResponseSchema.parse(await response.json())
		const total = data.hits.length
		const searchList: KbarListItem[] = data.hits.map((hit, index) => ({
			id: `post-${hit.post_id}`,
			label: hit.post_title,
			link: { internal: `/post/${hit.post_id}` },
			description: `${index + 1} / ${total}`,
		}))

		if (controller.signal.aborted) return
		const lists = { ...state.kbar.lists, [cacheKey]: searchList }
		updateKbarState({ lists })

		kbarTimer = setTimeout(() => {
			kbarTimer = null
			if (controller.signal.aborted) return
			updateKbarState({ list: searchList, loading: false })
		}, 500)
	} catch (error) {
		if (controller.signal.aborted) return
		console.error(error)
		updateKbarState({ list: [], loading: false })
	}
}
