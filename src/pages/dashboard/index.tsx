import Head from 'next/head'
import React from 'react'
import Content from '~/components/Content'
import Link from 'next/link'
import Icons from '~/components/Icons'
import GithubStarMetric from '~/components/Metrics/GithubStars'
import GithubFollowerMetric from '~/components/Metrics/GithubFollowers'
import PostsMetric from '~/components/Metrics/Posts'
import NexmentMetric from '~/components/Metrics/Nexment'
import SspaiMetric from '~/components/Metrics/Sspai'
import TwitterMetric from '~/components/Metrics/Twitter'
import ZhihuMetric from '~/components/Metrics/Zhihu'
import JMSMetric from '~/components/Metrics/JMS'

export default function Dashboard() {
  return (
    <div>
      <Head>
        <title>Dashboard - TonyHe</title>
        <link
          rel="icon"
          href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📊</text></svg>"
        ></link>
        <meta name="description" content="TonyHe's personal dashboard"></meta>
        <meta name="robots" content="noindex" />
      </Head>
      <Content>
        <div className="xl:mt-20 mt-0 xl:pt-0 pt-24">
          <div className="mb-4 flex items-center">
            <div className="flex-1 items-center">
              <h1 className="font-medium text-1 text-black dark:text-white tracking-wide">
                <span className="hover:animate-spin inline-block cursor-pointer mr-3">
                  📊
                </span>
                Dashboard
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
              My personal dashboard to keep track of metrics from various
              platforms
            </p>
          </div>
        </div>
        <div className="mt-5 mb-10 grid grid-cols-2 gap-4" data-cy="metricCards">
          <PostsMetric />
          <NexmentMetric />
          <GithubStarMetric />
          <GithubFollowerMetric />
          <ZhihuMetric />
          <TwitterMetric />
          <SspaiMetric />
          <JMSMetric />
        </div>
      </Content>
    </div>
  )
}
