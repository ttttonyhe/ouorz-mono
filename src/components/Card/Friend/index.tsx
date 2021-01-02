import Link from 'next/link'
import { DesSplit } from '~/utilities/String'

interface Props {
  item: any
}

export default function CardFriend({ item }: Props) {
  return (
    <div className="w-full shadow-sm bg-white rounded-md border mb-6">
      <div className="p-10 grid grid-flow-col grid-cols-3 gap-9">
        <div
          className="rounded-md shadow-sm h-full w-auto col-span-1 col-end-2 border border-gray-200"
          style={{
            backgroundImage: 'url(' + item.post_img.url + ')',
            backgroundSize: 'cover',
            backgroundRepeat: 'no-repeat',
            backgroundPosition: 'center',
          }}
        ></div>
        <div className="col-span-2 col-end-4">
          <Link href={`/post/${item.id}`}>
            <a>
              <h1 className="font-medium text-2 text-gray-700 tracking-wider mb-1">
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
    </div>
  )
}
