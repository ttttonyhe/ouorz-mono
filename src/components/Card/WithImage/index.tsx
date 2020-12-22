import Label from '~/components/Label'
import TimeAgo from 'react-timeago'

interface Props {
  item: any
  sticky: boolean
}

export default function CardWithImage({ item, sticky }: Props) {
  return (
    <div
      className={
        sticky
          ? 'w-full shadow-sm bg-white rounded-md border mb-6'
          : 'w-full shadow-sm bg-white rounded-md mb-6'
      }
    >
      <div className="p-10 grid grid-flow-col grid-cols-3 gap-9">
        <div
          className="rounded-md shadow-sm h-img min-h-full w-full col-span-1 col-end-2 border border-gray-200"
          style={{
            backgroundImage: 'url(' + item.post_img.url + ')',
            backgroundSize: 'cover',
            backgroundRepeat: 'no-repeat',
            backgroundPosition: 'center',
          }}
        ></div>
        <div className="col-span-2 col-end-4">
          <div className="flex space-x-3 items-center">
            <div className="flex space-x-2 col-start-1 col-end-3">
              {sticky && <Label name="sticky"></Label>}
              <Label name="primary" icon="cate">
                {item.post_categories[0].name}
              </Label>
            </div>
            <div>
              <Label name="secondary" icon="preview">
                Preview
              </Label>
            </div>
          </div>
          <div className="mt-4">
            <h1 className="font-medium text-listTitle text-gray-700 tracking-wider mb-5">
              {item.post_title}
            </h1>
            <p
              className="text-gray-500 text-xl tracking-wide leading-8"
              dangerouslySetInnerHTML={{
                __html: item.post_excerpt.four,
              }}
            ></p>
          </div>
        </div>
      </div>
      <div className="pt-2 pb-3 px-10 items-center w-full h-auto border-t rounded-br-md rounded-bl-md border-gray-100">
        <p className="flex space-x-2 text-lg tracking-wide leading-8 text-gray-500">
          <span>
            Posted <TimeAgo date={item.date} />
          </span>
          <span>·</span>
          <span>{item.post_metas.views} Views</span>
          <span>·</span>
          <span>ERT {item.post_metas.reading.time_required} min</span>
        </p>
      </div>
    </div>
  )
}
