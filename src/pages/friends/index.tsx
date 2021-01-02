import Head from 'next/head'
import React from 'react'
import Content from '~/components/Content'
import { GetServerSideProps } from 'next'
import List from '~/components/List'
import { getApi } from '~/utilities/Api'
import SubscriptionBox from '~/components/SubscriptionBox'
import Icons from '~/components/Icons'
import Link from 'next/link'

interface Sticky {
  stickyNotFound: boolean
  stickyPosts: any
  info: { name: string; count: number; id: number }
}

export default function Friends({ stickyNotFound, stickyPosts, info }: Sticky) {
  return (
    <div>
      <Head>
        <title>Friends - TonyHe</title>
      </Head>
      <Content>
        <div className="mt-20">
          <div className="mb-5 flex items-center">
            <div className="flex-1 items-center">
              <h1 className="font-medium text-1 text-black tracking-wide">
                <span className="hover:animate-spin inline-block cursor-pointer mr-3">
                  🧑‍🤝‍🧑
                </span>
                Friends
              </h1>
            </div>
            <div className="h-full flex justify-end whitespace-nowrap items-center mt-2">
              <div className="border-r border-r-gray-200 text-center flex-1 px-5">
                <p className="text-xl text-gray-500 flex items-center">
                  <span className="w-6 h-6 mr-2">{Icons.count}</span>
                  {info.count} sites
                </p>
              </div>
              <div className="flex-1 px-5">
                <p className="text-xl text-gray-500">
                  <Link href="/">
                    <a className="flex items-center">
                      <span className="w-6 h-6 mr-2">{Icons.left}</span>Home
                    </a>
                  </Link>
                </p>
              </div>
            </div>
          </div>
          <SubscriptionBox type="sm"></SubscriptionBox>
        </div>
        <div className="mt-5">
          {!stickyNotFound && <List posts={stickyPosts} sticky={true}></List>}
        </div>
        <div className="mt-5" data-cy="catePosts">
          <List type="cate" cate={2}></List>
        </div>
      </Content>
    </div>
  )
}

// Get sticky posts rendered on the server side
export const getServerSideProps: GetServerSideProps = async (context) => {
  const resSticky = await fetch(
    getApi({
      sticky: true,
      perPage: 10,
      cate: '2',
    })
  )
  const dataSticky = await resSticky.json()
  let stickyNotFound = false
  if (!dataSticky) {
    stickyNotFound = true
  }

  const resInfo = await fetch(
    getApi({
      cate: '2',
      getCate: true,
    })
  )
  const infoData = await resInfo.json()

  return {
    props: {
      stickyNotFound: stickyNotFound,
      stickyPosts: dataSticky,
      info: {
        name: infoData.name,
        count: infoData.count,
        id: infoData.id,
      },
    },
  }
}