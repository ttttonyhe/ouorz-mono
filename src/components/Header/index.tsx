import Button from '~/components/Button'
import React, { useState, useEffect } from 'react'
import Link from 'next/link'
import Image from 'next/image'
import { useRouter } from 'next/router'

export default function Header() {
  const router = useRouter()

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
      className={
        scrollPosition > 0
          ? 'transition-all duration-300 grid grid-cols-6 fixed top-0 h-auto w-full py-4 px-5 bg-white shadow-header z-10'
          : 'transition-all duration-300 grid grid-cols-6 fixed top-0 h-auto w-full py-4 px-5 z-10'
      }
    >
      <div className="col-start-1 col-end-2 flex xl:space-x-2">
        <Button bType="menu-default" icon="rss" className="hidden xl:flex">
          RSS
        </Button>
        {router.asPath.split('/').length > 2 ? (
          <Link href="/">
            <a>
              <Button bType="menu-default" icon="home">
                Home
              </Button>
            </a>
          </Link>
        ) : (
          <Button bType="menu-default" icon="search">
            Search
          </Button>
        )}
      </div>
      <div
        className={
          scrollPosition > 0
            ? 'col-start-3 col-end-5 items-center justify-center pt-1.5'
            : 'hidden'
        }
      >
        <div
          className="cursor-pointer mx-auto flex space-x-3 items-center justify-center"
          onClick={() => {
            router.push('/')
          }}
        >
          <div className="flex-shrink-0 h-7 w-7 border rounded-full border-gray-300">
            <Image
              className="rounded-full"
              src="/tony.jpg"
              alt="Logo"
              height="100%"
              width="100%"
            />
          </div>
          <div className="text-2xl font-medium text-black">
            <h3 className="text-gray-700">TonyHe</h3>
          </div>
        </div>
      </div>
      <div className="col-start-5 col-end-7 flex space-x-2 justify-end">
        <Button
          bType="menu-primary"
          icon="love"
          className="text-pink-500 hidden xl:flex"
        >
          Sponsor
        </Button>
        <Link href="/pages">
          <a>
            <Button
              bType="menu-default"
              icon="pages"
              className="hidden xl:flex"
            >
              Pages
            </Button>
          </a>
        </Link>
        <Link href="/post/126">
          <a>
            <Button bType="menu-default" icon="me">
              About
            </Button>
          </a>
        </Link>
      </div>
    </header>
  )
}
