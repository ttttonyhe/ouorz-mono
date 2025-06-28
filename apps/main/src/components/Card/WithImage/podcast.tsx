import { Label } from "@twilight-toolkit/ui"
import Image from "next/image"
import Link from "next/link"
import AudioPlayer from "react-h5-audio-player"
import "react-h5-audio-player/lib/styles.css"
import { Hover } from "~/components/Visual"
import blurDataURL from "~/constants/blurDataURL"
import { WPPost } from "~/constants/propTypes"
import { trimStr } from "~/utilities/string"

interface Props {
	item: WPPost
	sticky: boolean
}

const CardWithImagePodcast = ({ item, sticky }: Props) => {
	return (
		<div className="mb-6 w-full rounded-md border bg-white shadow-xs dark:border-gray-700 dark:bg-gray-800">
			<div className="pt-5 pr-5 pl-5 lg:grid lg:grid-flow-col lg:grid-cols-3 lg:gap-9 lg:pt-10 lg:pr-10 lg:pl-10">
				<Hover
					perspective={1000}
					max={25}
					scale={1.01}
					className="podcast-image-placeholder hidden rounded-md border border-gray-200 bg-gray-50 shadow-xs hover:shadow-md lg:block dark:opacity-90">
					<Image
						src={item.post_img.url}
						width={160}
						height={160}
						placeholder="blur"
						blurDataURL={blurDataURL}
						className="rounded-md"
						alt={`podcast-episode-cover-art-${item.post_title}`}
						loading="lazy"
					/>
				</Hover>
				<div className="col-span-2 col-end-4">
					<div className="mb-4 flex items-center space-x-3">
						<div className="col-start-1 col-end-3 flex space-x-2">
							{sticky && <Label type="sticky-icon" />}
							<Link href={`/cate/${item.post_categories[0].term_id}`}>
								<Label type="primary" icon="microphone">
									Episode {item.post_metas.podcast.episode}
								</Label>
							</Link>
						</div>
					</div>
					<a href={item.post_metas.podcast.episodeUrl}>
						<h1 className="text-2 lg:text-list-title mb-4 overflow-hidden font-medium tracking-wider text-ellipsis whitespace-nowrap text-gray-700 dark:text-white">
							{item.post_title}
						</h1>
					</a>
					<p
						className="text-4 lg:text-3 overflow-hidden leading-2 tracking-wide text-ellipsis text-gray-500 lg:leading-8 dark:text-gray-400"
						dangerouslySetInnerHTML={{
							__html: trimStr(item.post_excerpt.four, 80),
						}}
					/>
				</div>
			</div>
			<div className="px-2 pt-4 pb-4 lg:px-5">
				<AudioPlayer
					className="podcast-player focus:outline-hidden"
					autoPlayAfterSrcChange={false}
					src={item.post_metas.podcast.audioUrl}
					preload="metadata"
				/>
			</div>
		</div>
	)
}

export default CardWithImagePodcast
