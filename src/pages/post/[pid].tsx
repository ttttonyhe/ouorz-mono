import Head from 'next/head'
import Page from '~/components/Page'
import { GetServerSideProps } from 'next'
import { getApi } from '~/utilities/Api'
import SubscriptionBox from '~/components/SubscriptionBox'
import TimeAgo from 'react-timeago'
import CommentBox from '~/components/CommentBox'
import PostContent from '~/components/PostContent'

export default function BlogPost({ post }: { post: any }) {
  return (
    <div>
      <Head>
        <title>{post.title.rendered} - TonyHe</title>
      </Head>
      <Page>
        <article className="shadow-sm border rounded-xl bg-white p-10 lg:p-20">
          <div className="mb-20">
            <h1 className="text-postTitle font-medium tracking-wider leading-snug">
              {post.title.rendered}
            </h1>
            <p className="flex text-xl text-gray-500 space-x-2 mt-2 tracking-wide">
              <span>
                Posted <TimeAgo date={post.date} />
              </span>
              <span>·</span>
              <span>{post.post_metas.views} Views</span>
              <span>·</span>
              <span className="group">
                <span className="group-hover:hidden">
                  {post.post_metas.reading.word_count} Words
                </span>
                <span className="hidden group-hover:block">
                  ERT {post.post_metas.reading.time_required} min
                </span>
              </span>
            </p>
          </div>
          <PostContent content={post.content.rendered}></PostContent>
        </article>
        <div className="mt-5">
          <SubscriptionBox type="lg"></SubscriptionBox>
        </div>
        <CommentBox></CommentBox>
      </Page>
    </div>
  )
}

// Get sticky posts rendered on the server side
export const getServerSideProps: GetServerSideProps = async (context) => {
  const pid = context.params.pid

  // Increase page views
  await fetch(
    getApi({
      // @ts-ignore
      visit: pid,
    })
  )

  // Fetch page data
  const resData = await fetch(
    getApi({
      // @ts-ignore
      post: pid,
    })
  )
  const postData = await resData.json()

  return {
    props: {
      post: postData,
    },
  }
}
