import { useState, useEffect } from 'react'
import Icons from '~/components/Icons'
import { useRouter } from 'next/router'
import Link from 'next/link'
import smoothScroll from 'smoothscroll-polyfill'

export default function Aside({ preNext }: { preNext: any }) {
  const router = useRouter()
  const [headersResult, setHeadersResult] = useState<any>([])
  const [headersEl, setHeadersEl] = useState<any>([])

  const getAllHeaders = () => {
    const result: any = [[], []]
    const headerElements: any = []

    const toc: any = document.querySelector('#toc')
      ? document.querySelector('#toc').getElementsByTagName('li')
      : []

    for (let i = 0, n = toc.length; i < n; i++) {
      toc[i].classList.remove('toc-active')
    }

    const headers: any = document
      .querySelector('.prose')
      .getElementsByTagName('*')

    let minLevel: number

    for (let i = 0, n: number = headers.length; i < n; i++) {
      if (
        /^h\d{1}$/gi.test(headers[i].nodeName) &&
        headers[i].parentElement.className !== 'embed-content'
      ) {
        const headerLevel: number = parseInt(headers[i].tagName.substring(1, 2))
        const headerOffset: number = headers[i].offsetTop
        const headerContent: string = headers[i].innerText

        if (!minLevel || headerLevel <= minLevel) {
          minLevel = headerLevel
        }

        result[0].push([result[0].length, headerLevel, headerContent])
        result[1].push(headerOffset)
        headerElements.push(headers[i])
      }
    }

    for (let i = 0, n: number = result[0].length; i < n; i++) {
      result[0][i] = [
        result[0][i][0],
        (result[0][i][1] - minLevel) * 10,
        result[0][i][2],
      ]
    }

    let currentHeaderId = 1
    let currentHeaderOffset = result[1][1]
    let lastHeaderOffset = result[1][0]

    const handleScroll = () => {
      const scrollPosition = window.pageYOffset + 70

      if (scrollPosition >= currentHeaderOffset) {
        document
          .getElementById(`header${currentHeaderId - 1}`)
          .classList.remove('toc-active')
        document
          .getElementById(`header${currentHeaderId}`)
          .classList.add('toc-active')
        lastHeaderOffset = currentHeaderOffset
        currentHeaderId += 1
        currentHeaderOffset = result[1][currentHeaderId]
      } else if (scrollPosition < lastHeaderOffset) {
        if (currentHeaderId - 2 >= 0) {
          document
            .getElementById(`header${currentHeaderId - 1}`)
            .classList.remove('toc-active')
          document
            .getElementById(`header${currentHeaderId - 2}`)
            .classList.add('toc-active')
          currentHeaderId -= 1
          lastHeaderOffset = result[1][currentHeaderId - 1]
          currentHeaderOffset = result[1][currentHeaderId]
        } else {
          document.getElementById(`header0`).classList.remove('toc-active')
          currentHeaderId = 1
          currentHeaderOffset = result[1][1]
          lastHeaderOffset = result[1][0]
        }
      } else if (scrollPosition > lastHeaderOffset && currentHeaderId === 1) {
        document.getElementById(`header0`).classList.add('toc-active')
      }
    }

    return [result, handleScroll, headerElements]
  }

  const Tour = () => {
    const b =
      preNext['next'][0] && [58, 5, 2, 74].indexOf(preNext['next'][2]) === -1
    const a =
      preNext['prev'][0] && [58, 5, 2, 74].indexOf(preNext['prev'][2]) === -1
    if (a || b) {
      return (
        <div
          className={`bg-white text-gray-700 dark:bg-gray-800 dark:border-gray-800 dark:text-gray-400 shadow-sm border rounded-xl mt-5 text-xl grid ${
            a && b ? 'grid-cols-2' : 'grid-cols-1'
          } tour`}
        >
          {a && (
            <Link href={`/post/${preNext.prev[0]}`}>
              <div
                className={`px-6 py-3 flex items-center justify-center cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 ${
                  b ? 'rounded-tl-xl rounded-bl-xl' : 'rounded-xl'
                }`}
              >
                <span className="w-6 h-6 mr-2">{Icons.leftPlain}</span>Prev
              </div>
            </Link>
          )}
          {b && (
            <Link href={`/post/${preNext.next[0]}`}>
              <div
                className={`px-6 py-3 flex items-center justify-center cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 ${
                  a ? 'rounded-tr-xl rounded-br-xl' : 'rounded-xl'
                }`}
              >
                Next<span className="w-6 h-6 ml-2">{Icons.right}</span>
              </div>
            </Link>
          )}
        </div>
      )
    } else {
      return <div></div>
    }
  }

  const SubItem = ({
    item,
    inner,
    recursionTimes,
  }: {
    item: any
    inner: boolean
    recursionTimes: number
  }) => {
    if (inner) {
      return (
        <div
          className={`${
            recursionTimes == 0 ? 'border-l-0' : ''
          }toc-sub py-2 -my-2 text-gray-800 dark:text-gray-400 whitespace-nowrap overflow-ellipsis overflow-hidden cursor-pointer border-gray-100 dark:border-gray-600 hover:bg-gray-50 hover:rounded-md dark:hover:bg-gray-700`}
          style={{
            paddingLeft: recursionTimes == 0 ? '0px' : '10px',
            marginLeft: recursionTimes == 0 ? '0px' : '10px',
          }}
        >
          {recursionTimes > 0 ? (
            <SubItem
              item={item}
              inner={true}
              recursionTimes={recursionTimes - 1}
            ></SubItem>
          ) : (
            item[2]
          )}
        </div>
      )
    } else {
      return (
        <li
          className={`${
            item[1] !== 0
              ? 'toc-sub hover:rounded-tl-none hover:rounded-bl-none'
              : ''
          } py-2 text-gray-800 dark:text-gray-400 whitespace-nowrap overflow-ellipsis overflow-hidden cursor-pointer border-gray-100 dark:border-gray-600 hover:bg-gray-50 hover:rounded-md dark:hover:bg-gray-700`}
          id={`header${item[0]}`}
          style={{
            paddingLeft: '10px',
            marginLeft: item[1] !== 0 ? `10px` : '0px',
          }}
          key={item[0]}
          onClick={() => {
            headersEl[item[0]].scrollIntoView({
              behavior: 'smooth',
            })
          }}
        >
          {recursionTimes > 0 ? (
            <SubItem
              item={item}
              inner={true}
              recursionTimes={recursionTimes - 1}
            ></SubItem>
          ) : (
            item[2]
          )}
        </li>
      )
    }
  }

  useEffect(() => {
    const result = getAllHeaders()
    const handler = result[1]
    setHeadersResult(result[0][0])
    setHeadersEl(result[2])
    if (result[2].length) {
      window.addEventListener('scroll', handler)
    }
    smoothScroll.polyfill()
    return () => {
      window.removeEventListener('scroll', handler)
    }
  }, [router.asPath])

  return (
    <div className="w-toc fixed top-24 -ml-82 hidden xl:block overflow-hidden overflow-y-auto max-h-aside aside overscroll-contain">
      {headersEl.length ? (
        <div>
          <div className="shadow-sm border rounded-xl bg-white dark:bg-gray-800 dark:border-gray-800">
            <h1 className="flex text-2xl font-medium text-gray-700 dark:text-white tracking-wide items-center px-6 py-3 border-b border-gray-200 dark:border-gray-700">
              <span className="w-6 h-6 mr-2">{Icons.toc}</span>
              TOC
            </h1>
            <ul className="text-xl px-3 py-3" id="toc">
              {headersResult &&
                headersResult.map((item, index) => {
                  return (
                    <SubItem
                      key={index}
                      item={item}
                      inner={false}
                      recursionTimes={item[1] / 10}
                    ></SubItem>
                  )
                })}
            </ul>
          </div>
          <Tour></Tour>
        </div>
      ) : (
        ''
      )}
    </div>
  )
}
