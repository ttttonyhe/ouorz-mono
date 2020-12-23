import Button from '~/components/Button'
import React, { useState, useEffect } from 'react'
import Link from 'next/link'

export default function Header() {
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
          ? 'transition-all duration-300 grid grid-cols-6 fixed top-0 h-auto w-full py-4 px-5 bg-white shadow-header'
          : 'transition-all duration-300 grid grid-cols-6 fixed top-0 h-auto w-full py-4 px-5'
      }
    >
      <div className="col-start-1 col-end-2 flex xl:space-x-2">
        <Button bType="menu-default" icon="rss" className="hidden xl:flex">
          RSS
        </Button>
        <Button bType="menu-default" icon="search">
          Search
        </Button>
      </div>
      <div
        className={
          scrollPosition > 0
            ? 'col-start-3 col-end-5 items-center justify-center pt-1.5'
            : 'hidden'
        }
      >
        <div className="mx-auto flex space-x-3 items-center justify-center">
          <div className="flex-shrink-0">
            <img
              className="h-7 w-7 border rounded-full border-gray-300"
              src="/tony.jpg"
              alt="Logo"
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
          Donation
        </Button>
        <Link href="/page/765">
          <a>
            <Button bType="menu-default" icon="chat" className="hidden xl:flex">
              AMA
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
