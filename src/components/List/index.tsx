import dynamic from 'next/dynamic'

import CardWithImage from '~/components/Card/WithImage'
import CardWithOutImage from '~/components/Card/WithOutImage'
import CardPlainText from '~/components/Card/PlainText'
import CardEmpty from '~/components/Card/Empty'
const CardSkeleton = dynamic(() => import('~/components/Card/Skeleton'), {
  ssr: false,
})

import InfiniteScroll from 'react-infinite-scroll-component'
import { useSWRInfinite } from 'swr'
import { getApi } from '~/utilities/Api'

interface Props {
  posts?: any
  sticky?: boolean
  type?: string
  cate?: number
}

export default function List({ posts, sticky, type, cate }: Props) {
  if (posts) {
    return (
      <div key="Post list" data-cy={sticky ? 'stickyPostList' : 'postList'}>
        {posts.map((item) => {
          if (typeof item.code === 'undefined') {
            if (item.post_img.url) {
              return (
                <CardWithImage
                  item={item}
                  sticky={sticky}
                  key={item.id}
                ></CardWithImage>
              )
            } else if (item.post_categories[0].term_id === 58) {
              return (
                <CardPlainText
                  item={item}
                  sticky={sticky}
                  key={item.id}
                ></CardPlainText>
              )
            } else {
              return (
                <CardWithOutImage
                  item={item}
                  sticky={sticky}
                  key={item.id}
                ></CardWithOutImage>
              )
            }
          }
        })}
      </div>
    )
  } else {
    switch (type) {
      case 'index':
        return <InfiniteList type="index"></InfiniteList>
      case 'cate':
        return <InfiniteList type="cate" cate={cate}></InfiniteList>
      default:
        return <div key="Empty post list"></div>
    }
  }
}

const InfiniteList = ({ type, cate }: { type: string; cate?: number }) => {
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
        sticky: false,
        perPage: 10,
        cate: `${cate}`,
        cateExclude: '5,2,74',
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
      hasMore={!isReachingEnd}
      loader={<CardSkeleton></CardSkeleton>}
      endMessage={<CardEmpty></CardEmpty>}
      scrollThreshold="50px"
    >
      <List posts={postsData}></List>
    </InfiniteScroll>
  )
}
