import TimeAgo from 'react-timeago'
export default function BottomCard({ item }: { item: any }) {
  return (
    <div className="py-2 px-10 items-center w-full h-auto border-t rounded-br-md rounded-bl-md border-gray-100">
      <p className="flex space-x-2 text-4 tracking-wide leading-8 text-gray-500">
        <span>
          Posted <TimeAgo date={item.date} />
        </span>
        <span>·</span>
        <span>{item.post_metas.views} Views</span>
        <span>·</span>
        <span>ERT {item.post_metas.reading.time_required} min</span>
      </p>
    </div>
  )
}
