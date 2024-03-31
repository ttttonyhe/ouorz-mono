import React from "react"
import InfiniteScroll from "react-infinite-scroll-component"
import useSWRInfinite from "swr/infinite"
import CardEmpty from "~/components/Card/Empty"
import CardSkeleton from "~/components/Card/Skeleton"
import CardClickable from "~/components/Card/Clickable"
import getAPI from "~/utilities/api"
import StaticList from "./staticList"
import { ListTypes } from "~/constants/propTypes"

export interface InfiniteListProps {
	type: ListTypes
	cate?: number
	target?: string
}

/**
 * 	Determine the api url for fetching list data
 */
const getApiUrl = ({ type, cate, target }: InfiniteListProps) => {
	switch (type) {
		case "index":
			return getAPI("internal", "posts", {
				sticky: false,
				perPage: 10,
				cateExclude: "5,2,74,334,335",
			})
		case "cate":
			return getAPI("internal", "posts", {
				perPage: 10,
				cate,
				cateExclude: "5,2,74,334,335",
			})
		case "search":
			return getAPI("internal", "posts", {
				cateExclude: "5,2,74,334,335",
				search: target,
			})
		default:
			break
	}
}

const InfiniteList = (props: InfiniteListProps) => {
	const { type } = props
	const url = getApiUrl(props)
	const [stopLoading, setStopLoading] = React.useState<boolean>(false)

	const { data, error, size, setSize } = useSWRInfinite(
		(index) => `${url}&page=${index + 1}`,
		async (url) => {
			const res = await fetch(url)
			if (!res.ok) {
				throw new Error()
			}
			return res.json()
		}
	)
	const postData = data ? [].concat(...data) : []
	const isEmpty = data?.[0]?.length === 0
	const isReachingEnd =
		isEmpty || (data && data[data.length - 1]?.length < 10) || error

	return (
		<InfiniteScroll
			dataLength={postData.length}
			next={() => {
				setSize(size + 1)
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
			scrollableTarget={type === "search" ? "searchResultsDiv" : ""}
		>
			<StaticList posts={postData} />
		</InfiniteScroll>
	)
}

export default InfiniteList
