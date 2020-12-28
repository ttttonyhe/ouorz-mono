import Head from 'next/head'
import React from 'react'
import Content from '~/components/Content'
import Link from 'next/link'
import Icons from '~/components/Icons'
import PageCard from '~/components/PageCard'

export default function Pages() {
  return (
    <div>
      <Head>
        <title>TonyHe - Just A Poor Lifesinger</title>
      </Head>
      <Content>
        <div className="mt-20">
          <div className="mb-5 flex items-center">
            <div className="flex-1 items-center">
              <h1 className="font-medium text-1 text-black tracking-wide">
                <span className="hover:animate-spin inline-block cursor-pointer mr-3">
                  📑
                </span>
                Pages
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
            <p className="text-xl tracking-wide text-gray-500 whitespace-nowrap items-center">
              Explore and discover all the special pages and content
            </p>
          </div>
        </div>
        <div className="mt-5 grid grid-cols-2 gap-4">
          <PageCard
            title="AMA"
            des="Ask me anything"
            icon="chat"
            className="text-blue-600"
            href="/page/765"
          ></PageCard>
          <PageCard
            title="Comments"
            des="Leave a comment"
            icon="chat"
            className="text-gray-500"
            href="/page/249"
          ></PageCard>
          <PageCard
            title="Friends"
            des="Links to my friends' sites"
            icon="chat"
            className="text-green-500"
            href="/page/249"
          ></PageCard>
          <PageCard
            title="Sponsor"
            des="Buy me a coffee"
            icon="chat"
            className="text-pink-500"
            href="/page/765"
          ></PageCard>
          <PageCard
            title="DevDiary"
            des="Development Diary"
            icon="chat"
            className="text-gray-500"
            href="/page/249"
          ></PageCard>
        </div>
      </Content>
    </div>
  )
}
