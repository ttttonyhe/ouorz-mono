import React, { useState } from 'react'
import CardWithImage from '~/components/Card/WithImage'
import CardWithOutImage from '~/components/Card/WithOutImage'
import CardPlainText from '~/components/Card/PlainText'
import Reader from '~/components/Reader'

export interface StaticListProps {
	posts?: Post[]
	sticky?: boolean
}

export type Post = {
	code: any
	// eslint-disable-next-line camelcase
	post_img: { url: any }
	// eslint-disable-next-line camelcase
	post_categories: { term_id: number }[]
	id: string
}

const StaticList = ({ posts, sticky }: StaticListProps) => {
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
							return <CardPlainText item={item} sticky={sticky} key={item.id} />
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
}

export default StaticList
