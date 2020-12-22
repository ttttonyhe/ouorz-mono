import CardWithImage from '~/components/Card/WithImage'
import CardWithOutImage from '~/components/Card/WithOutImage'
import CardPlainText from '~/components/Card/PlainText'

interface Props {
  posts: any
  sticky?: boolean
}

export default function List({ posts, sticky }: Props) {
  if (posts) {
    return (
      <div key="Post list">
        {posts.map((item) => {
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
        })}
      </div>
    )
  } else {
    return <div key="Empty post list"></div>
  }
}
