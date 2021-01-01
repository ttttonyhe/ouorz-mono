import Head from 'next/head'
import React from 'react'
import Content from '~/components/Content'
import Link from 'next/link'
import Icons from '~/components/Icons'
import PageCard from '~/components/PageCard'

export default function Sponsor() {
  return (
    <div>
      <Head>
        <title>Sponsor - TonyHe</title>
      </Head>
      <Content>
        <div className="mt-20">
          <div className="mb-5 flex items-center">
            <div className="flex-1 items-center">
              <h1 className="font-medium text-1 text-black tracking-wide">
                <span className="hover:animate-spin inline-block cursor-pointer mr-3">
                  ☕
                </span>
                Sponsor
              </h1>
            </div>
            <div className="h-full flex justify-end whitespace-nowrap items-center mt-2">
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
          <div className="border shadow-sm w-full py-3 px-5 flex rounded-md bg-white items-center my-2">
            <p className="text-xl tracking-wide text-gray-500 items-center">
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
            className="text-black"
            href="/page/765"
          ></PageCard>
          <PageCard
            title="Podcast"
            des="Known Unknowns"
            icon="mic"
            className="text-black"
            href="/page/249"
          ></PageCard>
        </div>
        <div className="border shadow-sm w-full py-3 px-5 flex rounded-md bg-white items-center my-2">
          <p className="text-xl tracking-wide text-gray-500 items-center">
            If you{"'"}ve found my projects or podcast useful or interesting,
            please consider supporting me through the following ways
          </p>
        </div>
        <div className="mt-5 grid grid-cols-2 gap-4">
          <PageCard
            title="Alipay"
            des="helipeng_tony"
            icon="alipay"
            className="text-blue-500"
            href="/page/765"
          ></PageCard>
          <PageCard
            title="Wechat Pay"
            des="Helipeng_tony"
            icon="wxpay"
            className="text-green-600"
            href="/page/249"
          ></PageCard>
        </div>
      </Content>
    </div>
  )
}
