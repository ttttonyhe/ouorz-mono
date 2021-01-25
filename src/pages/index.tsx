import Head from 'next/head'
import React from 'react'
import Content from '~/components/Content'
import List from '~/components/List'
import Top from '~/components/Top'

export default function Home() {
  return (
    <div>
      <Head>
        <title>TonyHe</title>
        <link rel="icon" type="image/x-icon" href="/favicon.ico"></link>
        <meta
          name="description"
          content="A developer, blogger, podcaster"
        ></meta>
        <meta
          name="keywords"
          content="TonyHe, Lipeng He, Tony, Developer, Blogger, Podcaster"
        ></meta>
      </Head>
      <Content>
        <div className="xl:mt-20 mt-0 xl:pt-0 pt-24">
          <div>
            <h1 className="font-medium text-3xl leading-14 xl:text-1 text-black dark:text-white tracking-wide mb-0.5">
              <span className="hover:animate-spin inline-block cursor-pointer">
                👋
              </span>{' '}
              Hi, I{"'"}m TonyHe
            </h1>
            <p className="text-3 xl:text-2 text-gray-500 dark:text-gray-200 leading-14 tracking-wide font-light">
              I{"'"}m a developer, blogger, podcaster and undergraduate student
              at the University of Waterloo, Class of 2025, Honors Mathematics
            </p>
          </div>
          <Top></Top>
        </div>
        <div className="mt-10">
          <List type="index"></List>
        </div>
      </Content>
    </div>
  )
}
