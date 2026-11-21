import { useEffect, useRef, useState } from "react"
import useSWRInfinite from "swr/infinite"
import CardClickable from "~/components/Card/Clickable"
import CardEmpty from "~/components/Card/Empty"
import CardSkeleton from "~/components/Card/Skeleton"
import {
	type ListTypes,
	type WPPost,
	wpPostSchema,
} from "~/constants/propTypes"
import createFetcher from "~/lib/fetcher"
import api from "~/utilities/api"

import StaticList from "./staticList"

const fetchPosts = createFetcher(wpPostSchema.array())

export interface InfiniteListProps {
	type: ListTypes
	cate?: number
	target?: string
	initialPosts?: WPPost[]
}

const getApiUrl = ({ type, cate, target }: InfiniteListProps): string => {
	switch (type) {
		case "index":
			return api.internal.posts({
				sticky: false,
				perPage: 10,
				cateExclude: "5,2,74,334,335",
			})
		case "cate": {
			const params: Parameters<typeof api.internal.posts>[0] = { perPage: 10 }
			if (cate !== undefined) params.cate = cate
			return api.internal.posts(params)
		}
		case "search": {
			const params: Parameters<typeof api.internal.posts>[0] = {
				cateExclude: "5,2,74,334,335",
			}
			if (target !== undefined) params.search = target
			return api.internal.posts(params)
		}
	}
}

const InfiniteList = (props: InfiniteListProps) => {
	const { type, initialPosts } = props
	const url = getApiUrl(props)
	const [stopLoading, setStopLoading] = useState(false)
	const sentinelRef = useRef<HTMLDivElement>(null)

	const { data, error, isValidating, setSize } = useSWRInfinite<
		WPPost[],
		Error
	>(
		(index: number, previousPage: WPPost[] | null) =>
			!url || (previousPage && previousPage.length < 10)
				? null
				: `${url}&page=${index + 1}`,
		fetchPosts,
		initialPosts ? { fallbackData: [initialPosts] } : undefined
	)
	const postData = data ? data.flat() : []
	const isEmpty = data?.[0]?.length === 0
	const lastPage = data?.at(-1)
	const isReachingEnd =
		isEmpty ||
		(lastPage !== undefined && lastPage.length < 10) ||
		Boolean(error)
	const hasMore = !isReachingEnd && !stopLoading

	useEffect(() => {
		const target = sentinelRef.current
		if (!target || !hasMore) return

		const root =
			type === "search" ? document.querySelector("#searchResultsDiv") : null
		const observer = new IntersectionObserver(
			(entries) => {
				if (entries[0]?.isIntersecting && !isValidating) {
					void setSize((current) => current + 1)
				}
			},
			{ root, rootMargin: "100px" }
		)
		observer.observe(target)
		return () => observer.disconnect()
	}, [hasMore, isValidating, setSize, type])

	return (
		<div>
			<StaticList posts={postData} />
			{hasMore ? (
				<div ref={sentinelRef}>
					<CardClickable
						setStopLoading={setStopLoading}
						stopLoading={stopLoading}
					/>
					<CardSkeleton />
				</div>
			) : !isReachingEnd && stopLoading ? (
				<CardClickable
					setStopLoading={setStopLoading}
					stopLoading={stopLoading}
				/>
			) : (
				<CardEmpty />
			)}
		</div>
	)
}

export default InfiniteList
