import { useEffect, useRef, useState } from "react"

import { BlogContract, type Category } from "../lib/blog-contract"
import { getEthereum, walletError, type EthereumProvider } from "../lib/wallet"

interface BlogState {
	account: string
	blogUri: string
	authorAddress: string
	categories: Category[]
	errorMessage: string | null
}

const emptyState: BlogState = {
	account: "",
	blogUri: "",
	authorAddress: "",
	categories: [],
	errorMessage: null,
}

export const useBlogSession = () => {
	const [state, setState] = useState(emptyState)
	const session = useRef<BlogContract | null>(null)
	const reconnect = useRef<(() => Promise<void>) | null>(null)
	const connectionError = useRef<Error | null>(null)
	const operation = useRef<object | null>(null)
	const mounted = useRef(false)
	const connecting = useRef(false)

	useEffect(() => {
		mounted.current = true
		let ethereum: EthereumProvider | undefined
		let generation = 0
		let active = true

		const reset = () => {
			generation += 1
			session.current?.close()
			session.current = null
			connectionError.current = null
			operation.current = null
			setState(emptyState)
		}

		const refresh = async () => {
			reset()
			const attempt = generation
			try {
				const provider = getEthereum()
				if (!provider) throw new Error("Install an Ethereum wallet to connect.")
				if (provider !== ethereum) {
					detach()
					ethereum = provider
					ethereum.on("accountsChanged", onChange)
					ethereum.on("chainChanged", onChange)
					ethereum.on("disconnect", reset)
				}
				const connected = await BlogContract.connect(provider)
				if (!active || attempt !== generation) {
					connected?.close()
					return
				}
				session.current = connected ?? null
				setState({ ...emptyState, account: connected?.account ?? "" })
			} catch (error) {
				if (active && attempt === generation)
					connectionError.current = walletError.parse(error)
			}
		}

		const onChange = () => {
			void refresh()
		}
		const detach = () => {
			ethereum?.removeListener("accountsChanged", onChange)
			ethereum?.removeListener("chainChanged", onChange)
			ethereum?.removeListener("disconnect", reset)
		}
		reconnect.current = refresh
		void refresh()

		return () => {
			mounted.current = false
			active = false
			generation += 1
			session.current?.close()
			session.current = null
			reconnect.current = null
			detach()
		}
	}, [])

	const connect = async () => {
		if (connecting.current || operation.current) return
		connecting.current = true
		setState((previous) => ({ ...previous, errorMessage: null }))
		try {
			const ethereum = getEthereum()
			if (!ethereum) throw new Error("Install an Ethereum wallet to connect.")
			await ethereum.request({ method: "eth_requestAccounts" })
			await reconnect.current?.()
			if (connectionError.current) throw connectionError.current
		} catch (error) {
			if (mounted.current) {
				const message = walletError.parse(error).message
				setState((previous) => ({ ...previous, errorMessage: message }))
			}
		} finally {
			connecting.current = false
		}
	}

	const run = async (
		action: (blog: BlogContract) => Promise<Partial<BlogState>>
	) => {
		if (operation.current || connecting.current) return
		const current = session.current
		if (!current) {
			const message =
				connectionError.current?.message ?? "Connect your wallet first."
			setState((previous) => ({ ...previous, errorMessage: message }))
			return
		}
		const token = {}
		operation.current = token
		setState((previous) => ({ ...previous, errorMessage: null }))
		try {
			const update = await action(current)
			if (session.current === current)
				setState((previous) => ({ ...previous, ...update, errorMessage: null }))
		} catch (error) {
			if (session.current === current) {
				const message = walletError.parse(error).message
				setState((previous) => ({ ...previous, errorMessage: message }))
			}
		} finally {
			if (operation.current === token) operation.current = null
		}
	}

	return {
		...state,
		connect,
		readUri: () => run(async (blog) => ({ blogUri: await blog.getBlogUri() })),
		readAuthor: () =>
			run(async (blog) => ({ authorAddress: await blog.getAuthor() })),
		readCategories: () =>
			run(async (blog) => ({ categories: await blog.getCategories() })),
		updateUri: () =>
			run(async (blog) => {
				await blog.updateBlogUri("newUri")
				return { blogUri: await blog.getBlogUri() }
			}),
		createCategory: () =>
			run(async (blog) => {
				await blog.createDemoCategory()
				return { categories: await blog.getCategories() }
			}),
	}
}
