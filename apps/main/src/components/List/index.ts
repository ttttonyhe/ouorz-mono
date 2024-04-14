import InfiniteList from "./infiniteList"
import List from "./list"
import StaticList from "./staticList"

export type { ListProps } from "./list"
export type { ListTypes } from "~/constants/propTypes"
export type { InfiniteListProps } from "./infiniteList"
export type { StaticListProps } from "./staticList"

export type ListComponentType = typeof List & {
	Static: typeof StaticList
	Infinite: typeof InfiniteList
}
;(List as ListComponentType).Static = StaticList
;(List as ListComponentType).Infinite = InfiniteList

export default List as ListComponentType
