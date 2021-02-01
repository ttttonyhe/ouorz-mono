import Head from 'next/head'
import React from 'react'
import Content from '~/components/Content'
import Link from 'next/link'
import Icons from '~/components/Icons'
import PageCard from '~/components/PageCard'
import { getApi } from '~/utilities/Api'
import { GetStaticProps } from 'next'

export default function Sponsor({ sponsors }: { sponsors: any }) {
  return (
    <div>
      <Head>
        <title>Sponsor - TonyHe</title>
        <link
          rel="icon"
          href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>☕</text></svg>"
        ></link>
        <meta name="description" content="TonyHe's Supporters"></meta>
      </Head>
      <Content>
        <div className="xl:mt-20 mt-0 xl:pt-0 pt-24">
          <div className="mb-4 flex items-center">
            <div className="flex-1 items-center">
              <h1 className="font-medium text-1 text-black dark:text-white tracking-wide">
                <span className="hover:animate-spin inline-block cursor-pointer mr-3">
                  ☕
                </span>
                Sponsor
              </h1>
            </div>
            <div className="h-full flex justify-end whitespace-nowrap items-center mt-2">
              <div className="flex-1 px-5">
                <p className="text-xl text-gray-500 dark:text-gray-400">
                  <Link href="/">
                    <a className="flex items-center">
                      <span className="w-6 h-6 mr-2">{Icons.left}</span>Home
                    </a>
                  </Link>
                </p>
              </div>
            </div>
          </div>
          <div className="border shadow-sm w-full py-3 px-5 flex rounded-md bg-white dark:bg-gray-800 dark:border-gray-800 items-center my-2">
            <p className="text-xl tracking-wide text-gray-500 dark:text-gray-400 items-center">
              I am developing and maintaining various open source projects and
              hosting a podcast about tech, life and career
            </p>
          </div>
        </div>
        <div className="mt-5 mb-10 grid grid-cols-2 gap-4">
          <PageCard
            title="Github"
            des="HelipengTony"
            icon="githubLine"
            className="text-black dark:text-white"
            href="https://github.com/HelipengTony"
          ></PageCard>
          <PageCard
            title="Podcast"
            des="Known Unknowns"
            icon="mic"
            className="text-black dark:text-white"
            href="https://anchor.fm/the-known-unknowns"
          ></PageCard>
        </div>
        <div className="border shadow-sm w-full p-7 rounded-md bg-white dark:bg-gray-700 dark:border-gray-700 items-center my-2 mb-10">
          <p className="text-xl tracking-wide text-gray-500 dark:text-gray-300 items-center">
            If you{"'"}ve found my projects or podcast useful or interesting,
            please consider supporting me through the following ways:
          </p>
          <div className="mt-5 grid grid-cols-2 gap-4">
            <PageCard
              title="Alipay"
              des="helipeng_tony"
              icon="alipay"
              className="text-blue-500"
              href="https://static.ouorz.com/alipay.png"
            ></PageCard>
            <PageCard
              title="Wechat Pay"
              des="Helipeng_tony"
              icon="wxpay"
              className="text-green-600"
              href="https://static.ouorz.com/wechatpay.png"
            ></PageCard>
          </div>
          <div className="mt-4">
            <PageCard
              title="Bitcoin"
              des="bc1qz2kgqp26wtel6n7rl0cw053pxgtwt5vrr5hyd7pqmmjfhqxex8dq8fknpx"
              iconSmall="bitcoin"
              className="text-yellow-500"
              href="https://static.ouorz.com/bitcoin.jpg"
            ></PageCard>
          </div>
        </div>
        <div className="border shadow-sm w-full py-3 px-5 flex rounded-md bg-white dark:bg-gray-800 dark:border-gray-800 items-center my-2">
          <p className="text-xl tracking-wide text-gray-500 dark:text-gray-400 items-center">
            Contact me after finishing your payment, and I{"'"}ll put your name
            on the list below
          </p>
        </div>
        <div className="mt-5 grid grid-cols-2 gap-4" data-cy="sponsorsItems">
          {sponsors.map((item, index) => {
            return (
              <div
                key={index}
                className="cursor-pointer hover:shadow-md transition-shadow shadow-sm border py-4 px-5 bg-white dark:bg-gray-800 dark:border-gray-800 flex items-center rounded-md"
              >
                <div className="w-full flex items-center whitespace-nowrap overflow-hidden overflow-ellipsis">
                  <h1 className="flex-1 items-center text-xl tracking-wide font-medium">
                    {item.name}
                  </h1>
                  <p className="text-4 text-gray-400 tracking-wide justify-end items-center flex">
                    <span className="hidden xl:flex">
                      {item.date}&nbsp;|&nbsp;
                    </span>
                    <span className="text-gray-700 dark:text-white">
                      {item.unit}
                      {item.amount}
                    </span>
                  </p>
                </div>
              </div>
            )
          })}
        </div>
      </Content>
    </div>
  )
}

export const getStaticProps: GetStaticProps = async () => {
  const res = await fetch(
    getApi({
      sponsor: true,
    })
  )
  const data = await res.json()

  if (!data) {
    return {
      notFound: true,
    }
  }

  return {
    revalidate: 5 * 24 * 60 * 60,
    props: {
      sponsors: data.donors,
    },
  }
}
