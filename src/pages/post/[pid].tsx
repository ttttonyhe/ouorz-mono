import Head from 'next/head'
import Page from '~/components/Page'
import { GetServerSideProps } from 'next'
import { getApi } from '~/utilities/Api'
import SubscriptionBox from '~/components/SubscriptionBox'
import TimeAgo from 'react-timeago'
import CommentBox from '~/components/CommentBox'
import PostContent from '~/components/PostContent'
import Aside from '~/components/Aside'
import Link from 'next/link'
import Label from '~/components/Label'
import { CardTool } from '~/components/Card/WithImage/tool'
import { DesSplit } from '~/utilities/String'

export default function BlogPost({ post }: { post: any }) {
  return (
    <div>
      <Head>
        <title>{post.title.rendered} - TonyHe</title>
        <link
          rel="icon"
          href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📃</text></svg>"
        ></link>
        <meta
          name="description"
          content={DesSplit({ str: post.post_excerpt.four, n: 150 })}
        ></meta>
      </Head>
      <Page>
        <article className="xl:shadow-sm xl:border xl:rounded-xl bg-white dark:bg-gray-800 dark:border-gray-800 p-5 xl:p-20 xl:pt-20 pt-24">
          <div className="mb-20">
            <div className="flex mb-3">
              <Link href={`/cate/${post.post_categories[0].term_id}`}>
                <a>
                  <Label name="primary" icon="cate">
                    {post.post_categories[0].name}
                  </Label>
                </a>
              </Link>
            </div>
            <h1 className="text-1.5 xl:text-postTitle font-medium tracking-wider leading-snug">
              {post.title.rendered}
            </h1>
            <p className="flex text-5 xl:text-xl text-gray-500 space-x-2 mt-2 tracking-wide whitespace-nowrap">
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
          {post.post_categories[0].term_id === 4 && (
            <div className="mt-12">
              <CardTool item={post} preview={false}></CardTool>
            </div>
          )}
        </article>
        <Aside preNext={post.post_prenext}></Aside>
        <div className="xl:mt-5 border-t border-gray-200 xl:border-none">
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
