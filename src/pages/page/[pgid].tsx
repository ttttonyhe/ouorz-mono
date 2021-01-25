import Head from 'next/head'
import Page from '~/components/Page'
import { GetServerSideProps } from 'next'
import { getApi } from '~/utilities/Api'
import SubscriptionBox from '~/components/SubscriptionBox'
import TimeAgo from 'react-timeago'
import CommentBox from '~/components/CommentBox'
import PostContent from '~/components/PostContent'

export default function BlogPage({ page }: { page: any }) {
  return (
    <div>
      <Head>
        <title>{page.title.rendered} - TonyHe</title>
        <link
          rel="icon"
          href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📄</text></svg>"
        ></link>
        <meta name="description" content={page.title.rendered}></meta>
      </Head>
      <Page>
        <article className="xl:shadow-sm xl:border xl:rounded-xl bg-white dark:bg-gray-800 dark:border-gray-800 p-5 xl:p-20 pt-24">
          <div className="mb-20">
            <h1 className="text-1.5 xl:text-postTitle font-medium tracking-wider leading-snug">
              {page.title.rendered}
            </h1>
            <p className="flex text-5 xl:text-xl text-gray-500 dark:text-gray-400 space-x-2 mt-2 tracking-wide">
              <span>
                Posted <TimeAgo date={page.date} />
              </span>
              <span>·</span>
              <span>{page.post_metas.views} Views</span>
            </p>
          </div>
          <PostContent content={page.content.rendered}></PostContent>
        </article>
        <div className="mt-5">
          <SubscriptionBox type="lg"></SubscriptionBox>
        </div>
        <CommentBox></CommentBox>
      </Page>
    </div>
  )
}

export const getServerSideProps: GetServerSideProps = async (context) => {
  const pgid = context.params.pgid

  // Increase page views
  await fetch(
    getApi({
      // @ts-ignore
      visit: pgid,
    })
  )

  // Fetch page data
  const resData = await fetch(
    getApi({
      // @ts-ignore
      page: pgid,
    })
  )
  const pageData = await resData.json()

  return {
    props: {
      page: pageData,
    },
  }
}
