import Head from 'next/head'
import React from 'react'
import Page from '~/components/Page'
import Content from '~/components/Content'
import { GetServerSideProps } from 'next'
import List from '~/components/List'
import { getApi } from '~/utilities/Api'
import SubscriptionBox from '~/components/SubscriptionBox'
import Icons from '~/components/Icons'
import Link from 'next/link'

interface Sticky {
  page: any
}

export default function Cate({ page }: Sticky) {
  return (
    <div>
      <Head>
        <title>{page.title.rendered} - TonyHe</title>
      </Head>
      <Page>
        <div>
          <div>
            <title>{page.title.rendered}</title>
          </div>
          <article>{page.content.rendered}</article>
          <div>
            <SubscriptionBox type="lg"></SubscriptionBox>
          </div>
        </div>
      </Page>
    </div>
  )
}

// Get sticky posts rendered on the server side
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
