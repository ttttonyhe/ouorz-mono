import Label from '~/components/Label'
import { DesSplit } from '~/utilities/String'
import Link from 'next/link'
import AudioPlayer from 'react-h5-audio-player'
import 'react-h5-audio-player/lib/styles.css'
import Image from 'next/image'

interface Props {
  item: any
  sticky: boolean
}

export default function CardWithImagePodcast({ item, sticky }: Props) {
  return (
    <div className="w-full shadow-sm bg-white rounded-md border mb-6">
      <div className="pt-10 pl-10 pr-10 grid grid-flow-col grid-cols-3 gap-9">
        <div>
          <Image
            src={item.post_img.url}
            width={160}
            height={160}
            quality={100}
            className="rounded-md shadow-sm"
          ></Image>
        </div>
        <div className="col-span-2 col-end-4">
          <div className="flex space-x-3 items-center mb-4">
            <div className="flex space-x-2 col-start-1 col-end-3">
              {sticky && <Label name="sticky"></Label>}
              <Link href={`/cate/${item.post_categories[0].term_id}`}>
                <a>
                  <Label name="primary" icon="microphone">
                    Episode {item.post_metas.podcast.episode}
                  </Label>
                </a>
              </Link>
            </div>
          </div>
          <a href={item.post_metas.podcast.episodeUrl}>
            <h1 className="font-medium text-listTitle text-gray-700 tracking-wider mb-4 overflow-hidden overflow-ellipsis whitespace-nowrap">
              {item.post_title}
            </h1>
          </a>
          <p
            className="text-gray-500 text-3 tracking-wide leading-8"
            dangerouslySetInnerHTML={{
              __html: DesSplit({ str: item.post_excerpt.four, n: 80 }),
            }}
          ></p>
        </div>
      </div>
      <div className="px-5 pt-4 pb-4">
        <AudioPlayer
          className="podcast-player"
          autoPlayAfterSrcChange={false}
          src={item.post_metas.podcast.audioUrl}
        />
      </div>
    </div>
  )
}
