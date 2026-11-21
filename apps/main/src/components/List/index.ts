import InfiniteList from "./infiniteList"
import StaticList from "./staticList"

export type { ListTypes } from "~/constants/propTypes"
export type { InfiniteListProps } from "./infiniteList"
export type { StaticListProps } from "./staticList"

const List = {
	Static: StaticList,
	Infinite: InfiniteList,
}

export default List
