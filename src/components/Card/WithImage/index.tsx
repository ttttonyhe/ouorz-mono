import Label from '~/components/Label'
import CardFooter from '~/components/Card/Footer'
import CardWithImageTool from '~/components/Card/WithImage/tool'
import trimStr from '~/utilities/trimString'
import Link from 'next/link'
import Image from 'next/image'
import CardWithImagePodcast from '~/components/Card/WithImage/podcast'
import { useDispatch } from '~/hooks'
import { setReaderRequest } from '~/store/reader/actions'
import { WPPost } from '~/constants/propTypes'

interface Props {
	item: WPPost
	sticky: boolean
}

export default function CardWithImage({ item, sticky }: Props) {
	const dispatch = useDispatch()

	if (typeof item.post_metas.fineTool === 'undefined') {
		if (item.post_categories[0].term_id === 120) {
			return <CardWithImagePodcast item={item} sticky={sticky} />
		} else {
			return (
				<div className="w-full shadow-sm bg-white dark:bg-gray-800 dark:border-gray-800 rounded-md border mb-6">
					<div className="p-5 lg:p-10 lg:grid lg:grid-flow-col lg:grid-cols-3 lg:gap-9">
						<div className="dark:opacity-90 lg:block relative hidden rounded-md shadow-sm h-img min-h-full w-full col-span-1 col-end-2 border border-gray-200">
							<Image
								src={item.post_img.url}
								layout="fill"
								objectFit="cover"
								placeholder="blur"
								blurDataURL={`${item.post_img.url}?imageMogr2/format/webp/blur/1x0/quality/1|imageslim`}
								className="rounded-md"
								alt={`featured-image-${item.post_title}`}
							/>
						</div>
						<div className="col-span-2 col-end-4">
							<div className="flex space-x-3 items-center">
								<div className="flex space-x-2 col-start-1 col-end-3">
									{sticky && <Label type="sticky" />}
									<Link href={`/cate/${item.post_categories[0].term_id}`}>
										<a>
											<Label type="primary" icon="cate">
												{item.post_categories[0].name}
											</Label>
										</a>
									</Link>
								</div>
								<div
									data-oa="click-postPreview"
									className="justify-end hidden lg:flex lg:w-auto w-full"
									onClick={() => {
										dispatch(setReaderRequest(item))
									}}
								>
									<Label type="secondary" icon="preview">
										Preview
									</Label>
								</div>
							</div>
							<div className="lg:mt-4 mt-6">
								<Link href={`/post/${item.id}`}>
									<a>
										<h1
											className="font-medium text-2 lg:text-listTitle text-gray-700 dark:text-white tracking-wider mb-5"
											dangerouslySetInnerHTML={{ __html: item.post_title }}
										/>
									</a>
								</Link>
								<p
									className="text-gray-500 dark:text-gray-400 text-4 lg:text-3 tracking-wide leading-2 lg:leading-8 overflow-hidden text-ellipsis"
									dangerouslySetInnerHTML={{
										__html: trimStr(item.post_excerpt.four, 150),
									}}
								/>
							</div>
						</div>
					</div>
					<CardFooter item={item} />
				</div>
			)
		}
	} else {
		return <CardWithImageTool item={item} sticky={sticky} />
	}
}
