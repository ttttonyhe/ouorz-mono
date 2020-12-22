import Head from 'next/head'
import React from 'react'
import Button from '~/components/Button'
import Page from '~/components/Page'
import Content from '~/components/Content'
import { GetServerSideProps } from 'next'
import List from '~/components/List'

interface Sticky {
  stickyNotFound: boolean
  stickyPosts: any
  notFound: boolean
  posts: any
}

export default function Home({
  stickyNotFound,
  stickyPosts,
  notFound,
  posts,
}: Sticky) {
  return (
    <div>
      <Head>
        <title>TonyHe - Just A Poor Lifesinger</title>
      </Head>
      <Page>
        <Content>
          <div className="mt-20">
            <div>
              <h1 className="font-medium text-top text-black tracking-wide mb-3">
                👋 Hi, I{"'"}m TonyHe
              </h1>
              <p className="text-2xl text-gray-500 tracking-wide font-light">
                I{"'"}m a developer, blogger and undergraduate student at the
                University of Waterloo, Class of 2025, Honors Mathematics
              </p>
            </div>
            <div className="mt-5 grid xl:grid-cols-5 lg:gap-3">
              <div className="grid-cols-3 gap-3 col-start-1 col-span-3 hidden lg:grid">
                <Button type="default" icon="github" className="text-gray-700">
                  <span className="tracking-normal">Github</span>
                </Button>
                <Button type="default" icon="twitter" className="text-blue-400">
                  <span className="tracking-normal">Twitter</span>
                </Button>
                <Button type="default" icon="email" className="text-gray-500">
                  <span className="tracking-normal">Email</span>
                </Button>
              </div>
              <div className="lg:col-start-4 lg:col-end-6">
                <Button type="primary" icon="right">
                  <span className="tracking-normal">More about me</span>
                </Button>
              </div>
            </div>
          </div>
          <div className="mt-10">
            {!stickyNotFound && <List posts={stickyPosts} sticky={true}></List>}
          </div>
          <div className="mt-5">{!notFound && <List posts={posts}></List>}</div>
        </Content>
      </Page>
    </div>
  )
}

export const getServerSideProps: GetServerSideProps = async () => {
  const resSticky = await fetch(
    `https://blog.ouorz.com/wp-json/wp/v2/posts?sticky=1&per_page=10&categories_exclude=5,2,74`
  )
  const resPosts = await fetch(
    `https://blog.ouorz.com/wp-json/wp/v2/posts?sticky=0&per_page=10&categories_exclude=5,2,74`
  )
  const dataSticky = await resSticky.json()
  const dataPosts = await resPosts.json()

  let stickyNotFound = false
  let notFound = false

  if (!dataSticky) {
    stickyNotFound = true
  }
  if (!dataPosts) {
    notFound = true
  }

  return {
    props: {
      stickyNotFound: stickyNotFound,
      stickyPosts: dataSticky,
      notFound: notFound,
      posts: dataPosts,
    },
  }
}
