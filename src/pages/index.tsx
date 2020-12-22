import Head from 'next/head'
import React from 'react'
import Button from '~/components/Button'
import Page from '~/components/Page'
import Content from '~/components/Content'

export default function Home() {
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
            <div className="mt-5 grid grid-cols-5 gap-3">
              <div className="grid grid-cols-3 gap-3 col-start-1 col-span-3">
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
              <div className="col-start-4 col-end-6">
                <Button type="primary" icon="right">
                  <span className="tracking-normal">More about me</span>
                </Button>
              </div>
            </div>
          </div>
        </Content>
      </Page>
    </div>
  )
}
