import React, { useState } from 'react'
import CardWithImage from '~/components/Card/WithImage'
import CardWithOutImage from '~/components/Card/WithOutImage'
import CardPlainText from '~/components/Card/PlainText'
import Reader from '~/components/Reader'
import { WPPost } from '~/constants/propTypes'

export interface StaticListProps {
	posts?: WPPost[]
	sticky?: boolean
}

const StaticList = ({ posts, sticky }: StaticListProps) => {
	return (
		<div>
			<div key="PostList" data-cy="indexPosts">
				{posts.map((item: WPPost) => {
					if (typeof item.code === 'undefined') {
						if (item.post_img.url) {
							return <CardWithImage item={item} sticky={sticky} key={item.id} />
						} else if (item.post_categories[0].term_id === 58) {
							return <CardPlainText item={item} sticky={sticky} key={item.id} />
						} else {
							return (
								<CardWithOutImage item={item} sticky={sticky} key={item.id} />
							)
						}
					}
				})}
			</div>
			<div>
				<Reader />
			</div>
		</div>
	)
}

export default StaticList
