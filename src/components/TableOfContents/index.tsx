import { useState, useEffect } from 'react'
import Icons from '~/components/Icons'

export default function TableOfContents() {
  const [headersResult, setHeadersResult] = useState<any>([])

  const getAllHeaders = () => {
    const result: any = [[], []]

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
        document.getElementById(
          `header${currentHeaderId - 1}`
        ).style.fontWeight = '400'
        document.getElementById(`header${currentHeaderId}`).style.fontWeight =
          '600'
        lastHeaderOffset = currentHeaderOffset
        currentHeaderId += 1
        currentHeaderOffset = result[1][currentHeaderId]
      } else if (scrollPosition < lastHeaderOffset) {
        if (currentHeaderId - 2 >= 0) {
          document.getElementById(
            `header${currentHeaderId - 1}`
          ).style.fontWeight = '400'
          document.getElementById(
            `header${currentHeaderId - 2}`
          ).style.fontWeight = '600'
          currentHeaderId -= 1
          lastHeaderOffset = result[1][currentHeaderId - 1]
          currentHeaderOffset = result[1][currentHeaderId]
        } else {
          document.getElementById(`header0`).style.fontWeight = '400'
          currentHeaderId = 1
          currentHeaderOffset = result[1][1]
          lastHeaderOffset = result[1][0]
        }
      } else if (scrollPosition > lastHeaderOffset && currentHeaderId === 1) {
        document.getElementById(`header0`).style.fontWeight = '600'
      }
    }

    return [result, handleScroll]
  }

  useEffect(() => {
    const result = getAllHeaders()
    const handler = result[1]
    setHeadersResult(result[0][0])
    window.addEventListener('scroll', handler)
    return () => {
      window.removeEventListener('scroll', handler)
    }
  }, [])

  return (
    <div className="bg-white shadow-sm border rounded-xl absolute">
      <h1 className="flex text-3xl font-medium text-gray-700 tracking-wide items-center px-5 py-5">
        <span className="w-9 h-9 mr-2">{Icons.toc}</span>
        Table of Contents
      </h1>
      <ul className="text-xl px-5">
        {headersResult &&
          headersResult.map((item) => {
            return (
              <li
                className="border-t border-gray-200 py-2 text-gray-800"
                id={`header${item[0]}`}
                style={{
                  paddingLeft: `${item[1]}px`,
                }}
                key={item[0]}
              >
                {item[2]}
              </li>
            )
          })}
      </ul>
    </div>
  )
}
