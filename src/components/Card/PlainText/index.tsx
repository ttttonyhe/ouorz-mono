import TimeAgo from 'react-timeago'

interface Props {
  item: any
  sticky: boolean
}

export default function CardPlainText({ item }: Props) {
  return (
    <div className="w-full shadow-sm bg-white rounded-md border mb-6">
      <div className="px-10 pt-10 pb-4">
        <h1 className="font-normal text-3xl text-gray-600 tracking-wider leading-10 mb-5">
          {item.post_title}
        </h1>
      </div>
      <div className="pt-2 pb-3 px-10 items-center w-full h-auto border-t rounded-br-md rounded-bl-md border-gray-100">
        <p className="flex space-x-2 text-lg tracking-wide leading-8 text-gray-500">
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
