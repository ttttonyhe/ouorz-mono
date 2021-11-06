import React, { useState } from 'react'

import CardWithImage from '~/components/Card/WithImage'
import CardWithOutImage from '~/components/Card/WithOutImage'
import CardPlainText from '~/components/Card/PlainText'
import CardEmpty from '~/components/Card/Empty'
import CardSkeleton from '~/components/Card/Skeleton'
import CardClickable from '~/components/Card/Clickable'
import Reader from '~/components/Reader'

import InfiniteScroll from 'react-infinite-scroll-component'
import useSWRInfinite from 'swr/infinite'
import { getApi } from '~/assets/utilities/Api'

interface Props {
	posts?: any
	sticky?: boolean
	type?: string
	cate?: number
	target?: string
}

type Post = {
	code: any
	// eslint-disable-next-line camelcase
	post_img: { url: any }
	// eslint-disable-next-line camelcase
	post_categories: { term_id: number }[]
	id: React.ReactText
}

export default function List({ posts, sticky, type, cate, target }: Props) {
	if (posts) {
		// Preview
		const [reader, setReader] = useState<any>({ status: false, post: [] })

		return (
			<div>
				<div key="PostList" data-cy="indexPosts">
					{posts.map((item: Post) => {
						if (typeof item.code === 'undefined') {
							if (item.post_img.url) {
								return (
									<CardWithImage
										item={item}
										sticky={sticky}
										key={item.id}
										setReader={setReader}
									/>
								)
							} else if (item.post_categories[0].term_id === 58) {
								return (
									<CardPlainText item={item} sticky={sticky} key={item.id} />
								)
							} else {
								return (
									<CardWithOutImage
										item={item}
										sticky={sticky}
										key={item.id}
										setReader={setReader}
									/>
								)
							}
						}
					})}
				</div>
				<div>
					<Reader data={reader} setReader={setReader} />
				</div>
			</div>
		)
	} else {
		switch (type) {
			case 'index':
				return <InfiniteList type="index" />
			case 'cate':
				return <InfiniteList type="cate" cate={cate} />
			case 'search':
				return <InfiniteList type="search" target={target} />
			default:
				return <div key="Empty post list" />
		}
	}
}

const InfiniteList = ({
	type,
	cate,
	target,
}: {
	type: string
	cate?: number
	target?: string
}) => {
	const [stopLoading, setStopLoading] = React.useState<boolean>(false)
	let url
	switch (type) {
		case 'index':
			url = getApi({
				sticky: false,
				perPage: 10,
				cateExclude: '5,2,74',
			})
			break
		case 'cate':
			url = getApi({
				perPage: 10,
				cate: `${cate}`,
				cateExclude: '5,2,74',
			})
			break
		case 'search':
			url = getApi({
				cateExclude: '5,2,74',
				search: target,
			})
			break
		default:
			url = getApi({
				sticky: true,
				perPage: 10,
				cateExclude: '5,2,74',
			})
			break
	}

	const { data, error, size, setSize } = useSWRInfinite(
		(index) => `${url}&page=${index + 1}`,
		(url) => fetch(url).then((res) => res.json())
	)
	const postsData = data ? [].concat(...data) : []
	const isEmpty = data?.[0]?.length === 0
	const isReachingEnd =
		isEmpty || (data && data[data.length - 1]?.length < 10) || error

	return (
		<InfiniteScroll
			dataLength={postsData.length}
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
			scrollThreshold="50px"
			scrollableTarget={type === 'search' ? 'searchResultsDiv' : ''}
		>
			<List posts={postsData} />
		</InfiniteScroll>
	)
}
