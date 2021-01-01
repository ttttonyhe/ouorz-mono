import { useState } from 'react'
import Icons from '~/components/Icons'
import List from '~/components/List'

export default function Search({
  startSearching,
  setStartSearching,
  endSearching,
  setEndSearching,
}: {
  startSearching: boolean
  endSearching: boolean
  setStartSearching: any
  setEndSearching: any
}) {
  const [searchContent, setSearchContent] = useState<string>('')
  const [searchResultsDisplay, setSearchResultsDisplay] = useState<boolean>(
    false
  )

  return (
    (startSearching || endSearching) && (
      <div>
        <div
          className={`reader-bg ${
            startSearching
              ? 'animate-searchBg'
              : endSearching
              ? 'animate-searchBgOut'
              : ''
          }`}
          onClick={() => {
            setEndSearching(true)
            setStartSearching(false)
            setTimeout(() => {
              setEndSearching(false)
              setSearchContent('')
              setSearchResultsDisplay(false)
            }, 250)
            document
              .getElementsByTagName('body')[0]
              .classList.remove('stop-scrolling')
          }}
        ></div>
        <div
          id="searchResultsDiv"
          className={`fixed overflow-y-auto overflow-hidden rounded-tl-xl reader rounded-tr-xl px-15 py-20 w-content mx-auto left-searchOffset top-0 mt-28 px-10 ${
            startSearching
              ? 'animate-search'
              : endSearching
              ? 'animate-searchOut'
              : ''
          }`}
        >
          <div className="flex mb-10">
            <span className="h-8 w-7 absolute mt-4 ml-4 text-gray-400">
              {Icons.search}
            </span>
            <input
              className="shadow-md rounded-lg hover:shadow-lg bg-white text-2 w-full h-auto px-14 py-3 focus:outline-none text-gray-700"
              placeholder="Blog Post Search"
              value={searchContent}
              onChange={(e) => {
                setSearchResultsDisplay(false)
                setSearchContent(e.target.value)
              }}
              onKeyPress={(e) => {
                if (e.key === 'Enter') {
                  setSearchResultsDisplay(true)
                }
              }}
            ></input>
          </div>
          <div>
            {searchResultsDisplay && (
              <List type="search" target={searchContent}></List>
            )}
          </div>
        </div>
      </div>
    )
  )
}
