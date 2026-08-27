import InfiniteList from "./infiniteList"
import List from "./list"
import StaticList from "./staticList"

export type { ListTypes } from "~/constants/propTypes"
export type { InfiniteListProps } from "./infiniteList"
export type { ListProps } from "./list"
export type { StaticListProps } from "./staticList"

export type ListComponentType = typeof List & {
	Static: typeof StaticList
	Infinite: typeof InfiniteList
}
const ListComponent: ListComponentType = Object.assign(List, {
	Static: StaticList,
	Infinite: InfiniteList,
})

export default ListComponent
