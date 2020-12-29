import Label from '~/components/Label'
import BottomCard from '~/components/Card/Bottom'
import { DesSplit } from '~/utilities/String'
import Link from 'next/link'

interface Props {
  item: any
  sticky: boolean
  setReader?: any
}

export const CardTool = ({
  item,
  preview,
  setReader,
}: {
  item: any
  preview: boolean
  setReader?: any
}) => {
  return (
    <div className="w-full whitespace-nowrap grid grid-cols-8 gap-3 rounded-md shadow-sm border border-gray-200 overflow-hidden">
      <div
        className={
          item.post_metas.fineTool.itemImgBorder
            ? 'col-start-1 col-end-2 rounded-tl-md rounded-bl-md border-r border-gray-200'
            : 'col-start-1 col-end-2 rounded-tl-md rounded-bl-md'
        }
        style={{
          backgroundImage: 'url(' + item.post_img.url + ')',
          backgroundSize: 'cover',
          backgroundRepeat: 'no-repeat',
          backgroundPosition: 'center',
        }}
      ></div>
      <div className="col-start-2 col-end-9 grid grid-cols-2 items-center py-2 pr-3">
        <div className="justify-center items-center">
          <h2 className="text-xl font-medium text-gray-600">
            {item.post_metas.fineTool.itemName}
          </h2>
          <p className="text-gray-500 overflow-ellipsis text-5">
            {item.post_metas.fineTool.itemDes}
          </p>
        </div>
        <div className="flex justify-end space-x-2">
          {preview && (
            <a
              onClick={() => {
                setReader({ status: true, post: item })
              }}
            >
              <Label name="gray" icon="preview"></Label>
            </a>
          )}
          <a
            href={item.post_metas.fineTool.itemLink}
            target="_blank"
            rel="noreferrer"
          >
            <Label name="green" icon="right" preview={preview}>
              {item.post_metas.fineTool.itemLinkName}
            </Label>
          </a>
        </div>
      </div>
    </div>
  )
}

export default function CardWithImageTool({ item, sticky, setReader }: Props) {
  return (
    <div
      className={`w-full shadow-sm bg-white rounded-md border ${
        sticky ? 'border-t-4 border-t-yellow-200 mb-6' : 'mb-6'
      }`}
    >
      <div className="p-10">
        <CardTool item={item} preview={true} setReader={setReader}></CardTool>
        <div className="mt-6">
          <Link href={`/post/${item.id}`}>
            <a>
              <h1 className="font-medium text-listTitle text-gray-700 tracking-wider mb-5">
                {item.post_title}
              </h1>
            </a>
          </Link>
          <p
            className="text-gray-500 text-3 tracking-wide leading-8"
            dangerouslySetInnerHTML={{
              __html: DesSplit({ str: item.post_excerpt.four, n: 150 }),
            }}
          ></p>
        </div>
      </div>
      <BottomCard item={item}></BottomCard>
    </div>
  )
}
