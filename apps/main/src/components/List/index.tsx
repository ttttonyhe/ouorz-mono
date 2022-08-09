import React, { Suspense } from 'react'
import ErrorBoundary from '~/components/ErrorBoundary'
import CardEmpty from '~/components/Card/Empty'
import CardSkeleton from '~/components/Card/Skeleton'
import { ListProps } from './list'
import StaticList from './staticList'
import InfiniteList from './infiniteList'

const ListContent = React.lazy(() => import('./list'))

export type { ListProps }
export type { ListTypes } from '~/constants/propTypes'
export type { InfiniteListProps } from './infiniteList'
export type { StaticListProps } from './staticList'

const List = (props: ListProps) => {
	return (
		<ErrorBoundary fallback={<CardEmpty />}>
			<Suspense fallback={<CardSkeleton />}>
				<ListContent {...props} />
			</Suspense>
		</ErrorBoundary>
	)
}

export type ListComponentType = typeof List & {
	Static: typeof StaticList
	Infinite: typeof InfiniteList
}
;(List as ListComponentType).Static = StaticList
;(List as ListComponentType).Infinite = InfiniteList

export default List as ListComponentType
