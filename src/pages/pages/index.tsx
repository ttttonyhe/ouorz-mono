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
        <title>Pages - TonyHe</title>
        <link
          rel="icon"
          href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📑</text></svg>"
        ></link>
        <meta name="description" content="TonyHe's blog pages"></meta>
      </Head>
      <Content>
        <div className="xl:mt-20 mt-0 xl:pt-0 pt-24">
          <div className="mb-4 flex items-center">
            <div className="flex-1 items-center">
              <h1 className="font-medium text-1 text-black dark:text-white tracking-wide">
                <span className="hover:animate-spin inline-block cursor-pointer mr-3">
                  📑
                </span>
                Pages
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
              Explore and discover all the special pages and content
            </p>
          </div>
        </div>
        <div className="mt-5 grid grid-cols-2 gap-4">
          <PageCard
            title="AMA"
            des="Ask me anything"
            icon="question"
            className="text-yellow-400"
            href="/page/765"
          ></PageCard>
          <PageCard
            title="Guestbook"
            des="Leave your comments"
            icon="chat"
            className="text-green-400"
            href="/page/249"
          ></PageCard>
          <PageCard
            title="DevDiary"
            des="Development Diary"
            icon="growth"
            className="text-blue-400"
            href="https://dev.ouorz.com"
          ></PageCard>
          <PageCard
            title="Friends"
            des="Links exchanged"
            icon="people"
            className="text-pink-400"
            href="/friends"
          ></PageCard>
          <PageCard
            title="Thoughts"
            des="Random but memorable"
            icon="lightBulb"
            className="text-red-400"
            href="https://notion.ouorz.com"
          ></PageCard>
          <PageCard
            title="Podcast"
            des="Known Unknowns"
            icon="mic"
            className="text-indigo-400"
            href="https://kukfm.com"
          ></PageCard>
        </div>
      </Content>
    </div>
  )
}
