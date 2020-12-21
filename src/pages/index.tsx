import Head from 'next/head'
import React from 'react'
import tw from 'twin.macro'

export default function Home() {
  const [current, setCurrent] = React.useState<string>('one')
  return (
    <div className="container">
      <Head>
        <title>Create Next App</title>
        <link rel="icon" href="/favicon.ico" />
      </Head>

      <main className="main">
        <h1 className="title">
          Welcome to <a href="https://nextjs.org">Next.js!</a>
        </h1>
      </main>

      <footer tw="mb-6 p-6 justify-items-center transition shadow-sm border-gray-200 h-auto rounded-md w-5/12 ml-auto mr-auto border-2 mb-10 cursor-pointer">
        <div>
          <a
            tw="flex justify-center items-center m-auto"
            href="https://vercel.com?utm_source=create-next-app&utm_medium=default-template&utm_campaign=create-next-app"
            target="_blank"
            rel="noopener noreferrer"
          >
            Powered by{' '}
            <img src="/vercel.svg" alt="Vercel Logo" className="logo" />
          </a>
        </div>
        <div>
          <p css={[tw`text-gray-800`, current === 'one' && tw`font-bold`]}>
            One
          </p>
          <p css={[tw`text-gray-800`, current === 'two' && tw`font-bold`]}>
            Two
          </p>
          <p css={[tw`text-gray-800`, current === 'three' && tw`font-bold`]}>
            Three
          </p>
        </div>
        <div tw="grid grid-cols-3 gap-3 w-full">
          <button
            tw="border border-gray-200 hover:border-gray-300 w-full rounded-md"
            onClick={() => {
              setCurrent('one')
            }}
          >
            One
          </button>
          <button
            tw="border border-gray-200 hover:border-gray-300 w-full rounded-md"
            onClick={() => {
              setCurrent('two')
            }}
          >
            Two
          </button>
          <button
            tw="border border-gray-200 hover:border-gray-300 w-full rounded-md"
            onClick={() => {
              setCurrent('three')
            }}
          >
            Three
          </button>
        </div>
      </footer>
    </div>
  )
}
