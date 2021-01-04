import Label from '~/components/Label'
import { DesSplit } from '~/utilities/String'
import Link from 'next/link'
import AudioPlayer from 'react-h5-audio-player'
import 'react-h5-audio-player/lib/styles.css'

interface Props {
  item: any
  sticky: boolean
}

export default function CardWithImagePodcast({ item, sticky }: Props) {
  return (
    <div className="w-full shadow-sm bg-white rounded-md border mb-6">
      <div className="xl:pt-10 pt-5 pl-5 pr-5 xl:pl-10 xl:pr-10 xl:grid xl:grid-flow-col xl:grid-cols-3 xl:gap-9">
        <div className="xl:block hidden">
          <img
            src={item.post_img.url}
            width={160}
            height={160}
            className="rounded-md shadow-sm border border-gray-200"
          ></img>
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
            <h1 className="font-medium xl:text-listTitle text-2 text-gray-700 tracking-wider mb-4 overflow-hidden overflow-ellipsis whitespace-nowrap">
              {item.post_title}
            </h1>
          </a>
          <p
            className="text-gray-500 text-4 xl:text-3 tracking-wide leading-2 xl:leading-8"
            dangerouslySetInnerHTML={{
              __html: DesSplit({ str: item.post_excerpt.four, n: 80 }),
            }}
          ></p>
        </div>
      </div>
      <div className="xl:px-5 px-2 pt-4 pb-4">
        <AudioPlayer
          className="podcast-player focus:outline-none"
          autoPlayAfterSrcChange={false}
          src={item.post_metas.podcast.audioUrl}
        />
      </div>
    </div>
  )
}
