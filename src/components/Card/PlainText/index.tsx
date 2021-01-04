import TimeAgo from 'react-timeago'
import Icons from '~/components/Icons'
import { getApi } from '~/utilities/Api'
import React from 'react'

interface Props {
  item: any
  sticky: boolean
}

export default function CardPlainText({ item }: Props) {
  const [marking, setMarking] = React.useState<boolean>(false)
  const [marks, setMarks] = React.useState<number>(item.post_metas.markCount)
  const markPost = async (id: number) => {
    await fetch(getApi({ mark: id })).then(async (res: any) => {
      const data = await res.json()
      console.log(data)
      setMarks(data.markCountNow)
      setMarking(false)
    })
  }

  return (
    <div className="w-full shadow-sm bg-white rounded-md border mb-6">
      <div className="px-5 py-5 xl:px-10 xl:py-9">
        <h1 className="font-normal text-2 xl:text-3xl text-gray-600 tracking-wider leading-2 xl:leading-10">
          {item.post_title}
        </h1>
      </div>
      <div className="pt-3 pb-3 px-5 xl:pt-2 xl:pb-3 xl:px-10 items-center w-full h-auto border-t rounded-br-md rounded-bl-md border-gray-100">
        <p className="flex space-x-2 text-5 xl:text-4 tracking-wide leading-2 xl:leading-8 text-gray-500 items-center">
          <span
            className="flex items-center space-x-1 text-red-400 hover:text-red-500 cursor-pointer rounded-md"
            onClick={() => {
              setMarking(true)
              markPost(item.id)
            }}
          >
            {marking ? (
              <i className="w-6 h-6 -mt-1">{Icons.loveFill}</i>
            ) : (
              <i className="w-6 h-6 -mt-1">{Icons.love}</i>
            )}
            <em className="not-italic">{marks}</em>
          </span>
          <span className="xl:block hidden">·</span>
          <span className="xl:block hidden">
            Posted <TimeAgo date={item.date} />
          </span>
          <span>·</span>
          <span
            dangerouslySetInnerHTML={{
              __html: item.post_metas.status,
            }}
          ></span>
        </p>
      </div>
    </div>
  )
}
