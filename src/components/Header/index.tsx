import Button from '~/components/Button'
import React, { useState, useEffect } from 'react'
import Link from 'next/link'
import { useRouter } from 'next/router'
import Search from '~/components/Search'

export default function Header() {
  const router = useRouter()

  const [startSearching, setStartSearching] = useState<boolean>(false)
  const [endSearching, setEndSearching] = useState<boolean>(false)

  const [scrollPosition, setScrollPosition] = useState(0)
  const handleScroll = () => {
    const position = window.pageYOffset
    setScrollPosition(position)
  }

  useEffect(() => {
    window.addEventListener('scroll', handleScroll, { passive: true })

    return () => {
      window.removeEventListener('scroll', handleScroll)
    }
  }, [])

  return (
    <header
      id="header"
      className={`leading-14 xl:border-0 border-b border-gray-200 dark:border-gray-800 transition-all xl:bg-transparent bg-white duration-300 grid grid-cols-6 fixed top-0 h-auto w-full xl:py-4 xl:px-5 py-2 px-1 z-10 ${
        scrollPosition > 0 ? 'xl:bg-white dark:bg-gray-800 shadow-header' : ''
      }`}
    >
      <div className="col-start-1 col-end-2 flex xl:space-x-2">
        <a
          href="https://blog.ouorz.com/feed/gn"
          target="_blank"
          rel="noreferrer"
        >
          <Button
            bType="menu-default"
            icon="rss"
            className="hidden xl:flex text-3"
          >
            RSS
          </Button>
        </a>
        <Button
          bType="menu-default"
          icon="search"
          className="text-3"
          onClick={() => {
            setStartSearching(!startSearching)
            document
              .getElementsByTagName('body')[0]
              .classList.add('stop-scrolling')
          }}
        >
          Search
        </Button>
      </div>
      <div
        className={
          scrollPosition > 0
            ? 'col-start-3 col-end-5 items-center justify-center pt-1'
            : 'hidden'
        }
      >
        <div
          className="cursor-pointer mx-auto hidden xl:flex space-x-3 items-center justify-center"
          onClick={() => {
            router.push('/')
          }}
        >
          <div className="flex-shrink-0 h-7 w-7 border rounded-full border-gray-300 dark:border-gray-500">
            <img
              className="rounded-full"
              src="/tony.jpg"
              alt="Logo"
              height="100%"
              width="100%"
            />
          </div>
          <div className="text-2 font-medium text-black">
            <h3 className="text-gray-700 dark:text-gray-300">TonyHe</h3>
          </div>
        </div>
      </div>
      <div className="col-start-5 col-end-7 flex space-x-2 justify-end">
        {router.asPath.split('/').length > 2 ? (
          <Link href="/">
            <a>
              <Button
                bType="menu-default"
                icon="home"
                className="text-3 hidden xl:flex"
              >
                Home
              </Button>
            </a>
          </Link>
        ) : (
          <Link href="/sponsor">
            <a>
              <Button
                bType="menu-primary"
                icon="love"
                className="text-pink-500 hidden xl:flex text-3"
              >
                Sponsor
              </Button>
            </a>
          </Link>
        )}
        <Link href="/pages">
          <a>
            <Button
              bType="menu-default"
              icon="pages"
              className="hidden xl:flex text-3"
            >
              Pages
            </Button>
          </a>
        </Link>
        <Link href="/post/126">
          <a>
            <Button bType="menu-default" icon="me" className="text-3">
              About
            </Button>
          </a>
        </Link>
      </div>
      <Search
        startSearching={startSearching}
        setStartSearching={setStartSearching}
        setEndSearching={setEndSearching}
        endSearching={endSearching}
      ></Search>
    </header>
  )
}
