import React from "react"
import InfiniteScroll from "react-infinite-scroll-component"
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

/**
 * 	Determine the api url for fetching list data
 */
const getApiUrl = ({ type, cate, target }: InfiniteListProps) => {
	switch (type) {
		case "index":
			return api.internal.posts({
				sticky: false,
				perPage: 10,
				cateExclude: "5,2,74,334,335",
			})
		case "cate":
			return api.internal.posts({
				perPage: 10,
				cate,
			})
		case "search":
			return api.internal.posts({
				cateExclude: "5,2,74,334,335",
				search: target,
			})
		default:
			break
	}
}

const InfiniteList = (props: InfiniteListProps) => {
	const { type, initialPosts } = props
	const url = getApiUrl(props)
	const [stopLoading, setStopLoading] = React.useState<boolean>(false)

	const { data, error, size, setSize } = useSWRInfinite<WPPost[], Error>(
		(index: number, previousPage: WPPost[] | null) =>
			!url || (previousPage && previousPage.length < 10)
				? null
				: `${url}&page=${index + 1}`,
		fetchPosts,
		{ fallbackData: initialPosts ? [initialPosts] : undefined }
	)
	const postData = data ? data.flat() : []
	const isEmpty = data?.[0]?.length === 0
	const lastPage = data?.at(-1)
	const isReachingEnd =
		isEmpty || (lastPage !== undefined && lastPage.length < 10) || !!error

	return (
		<InfiniteScroll
			key={url}
			dataLength={postData.length}
			next={() => {
				void setSize(size + 1)
			}}
			hasMore={!isReachingEnd && !stopLoading}
			loader={
				<div>
					<CardClickable
						setStopLoading={setStopLoading}
						stopLoading={stopLoading}
					/>
					<CardSkeleton />
				</div>
			}
			endMessage={
				!isReachingEnd && stopLoading ? (
					<CardClickable
						setStopLoading={setStopLoading}
						stopLoading={stopLoading}
					/>
				) : (
					<CardEmpty />
				)
			}
			scrollThreshold="100px"
			scrollableTarget={type === "search" ? "searchResultsDiv" : ""}>
			<StaticList posts={postData} />
		</InfiniteScroll>
	)
}

export default InfiniteList
