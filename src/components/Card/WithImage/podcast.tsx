import Label from '~/components/Label'
import { DesSplit } from '~/assets/utilities/String'
import Link from 'next/link'
import Image from 'next/image'
import AudioPlayer from 'react-h5-audio-player'
import 'react-h5-audio-player/lib/styles.css'

interface Props {
	item: any
	sticky: boolean
}

const CardWithImagePodcast = ({ item, sticky }: Props) => {
	return (
		<div className="w-full shadow-sm bg-white dark:bg-gray-800 dark:border-gray-800 rounded-md border mb-6">
			<div className="lg:pt-10 pt-5 pl-5 pr-5 lg:pl-10 lg:pr-10 lg:grid lg:grid-flow-col lg:grid-cols-3 lg:gap-9">
				<div className="lg:block hidden bg-gray-50 podcast-image-placeholder rounded-md shadow-sm border border-gray-200 dark:opacity-90">
					<Image
						src={item.post_img.url}
						width={160}
						height={160}
						placeholder="blur"
						blurDataURL={`${item.post_img.url}?imageMogr2/thumbnail/168x/format/webp/blur/1x0/quality/1|imageslim`}
						className="rounded-md"
						alt={`podcast-episode-cover-art-${item.post_title}`}
					/>
				</div>
				<div className="col-span-2 col-end-4">
					<div className="flex space-x-3 items-center mb-4">
						<div className="flex space-x-2 col-start-1 col-end-3">
							{sticky && <Label name="sticky" />}
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
						<h1 className="font-medium lg:text-listTitle text-2 text-gray-700 dark:text-white tracking-wider mb-4 overflow-hidden overflow-ellipsis whitespace-nowrap">
							{item.post_title}
						</h1>
					</a>
					<p
						className="text-gray-500 dark:text-gray-400 text-4 lg:text-3 tracking-wide leading-2 lg:leading-8 overflow-hidden overflow-ellipsis"
						dangerouslySetInnerHTML={{
							__html: DesSplit({ str: item.post_excerpt.four, n: 80 }),
						}}
					/>
				</div>
			</div>
			<div className="lg:px-5 px-2 pt-4 pb-4">
				<AudioPlayer
					className="podcast-player focus:outline-none"
					autoPlayAfterSrcChange={false}
					src={item.post_metas.podcast.audioUrl}
				/>
			</div>
		</div>
	)
}

export default CardWithImagePodcast
