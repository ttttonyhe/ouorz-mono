import Label from '~/components/Label'
import BottomCard from '~/components/Card/Bottom'
import CardWithImageTool from '~/components/Card/WithImage/tool'
import { DesSplit } from '~/utilities/String'
import Link from 'next/link'

interface Props {
  item: any
  sticky: boolean
}

export default function CardWithImage({ item, sticky }: Props) {
  if (typeof item.post_metas.fineTool === 'undefined') {
    return (
      <div className="w-full shadow-sm bg-white rounded-md border mb-6">
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
                <Link href={`/cate/${item.post_categories[0].term_id}`}>
                  <a>
                    <Label name="primary" icon="cate">
                      {item.post_categories[0].name}
                    </Label>
                  </a>
                </Link>
              </div>
              <div className="justify-end flex">
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
                  __html: DesSplit({ str: item.post_excerpt.four, n: 150 }),
                }}
              ></p>
            </div>
          </div>
        </div>
        <BottomCard item={item}></BottomCard>
      </div>
    )
  } else {
    return <CardWithImageTool item={item} sticky={sticky}></CardWithImageTool>
  }
}
