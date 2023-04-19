import React from "react"
import { ListTypes } from "~/constants/propTypes"
import InfiniteList from "./infiniteList"

export interface ListProps {
	type?: ListTypes
	cate?: number
	target?: string
}

const List = ({ type, cate, target }: ListProps) => {
	switch (type) {
		case "index":
			return <InfiniteList type="index" />
		case "cate":
			return <InfiniteList type="cate" cate={cate} />
		case "search":
			return <InfiniteList type="search" target={target} />
		default:
			return <div key="Empty post list" />
	}
}

export default List
