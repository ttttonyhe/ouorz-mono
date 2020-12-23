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
      <div className="px-10 pt-10 pb-4">
        <h1 className="font-normal text-3xl text-gray-600 tracking-wider leading-10 mb-5">
          {item.post_title}
        </h1>
      </div>
      <div className="pt-2 pb-3 px-10 items-center w-full h-auto border-t rounded-br-md rounded-bl-md border-gray-100">
        <p className="flex space-x-2 text-lg tracking-wide leading-8 text-gray-500 items-center">
          <span
            className="flex items-center space-x-1 text-red-400 hover:text-red-500 cursor-pointer rounded-md"
            onClick={() => {
              setMarking(true)
              markPost(item.id)
            }}
          >
            {marking ? (
              <i className="w-6 h-6">{Icons.loveFill}</i>
            ) : (
              <i className="w-6 h-6">{Icons.love}</i>
            )}
            <em className="not-italic">{marks}</em>
          </span>
          <span>·</span>
          <span>
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
