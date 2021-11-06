import Label from '~/components/Label'
import BottomCard from '~/components/Card/Bottom'
import CardWithImageTool from '~/components/Card/WithImage/tool'
import { DesSplit } from '~/assets/utilities/String'
import Link from 'next/link'
import Image from 'next/image'
import CardWithImagePodcast from '~/components/Card/WithImage/podcast'

interface Props {
	item: any
	sticky: boolean
	setReader: any
}

export default function CardWithImage({ item, sticky, setReader }: Props) {
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
								blurDataURL={`${item.post_img.url}?imageMogr2/thumbnail/168x/format/webp/blur/1x0/quality/1|imageslim`}
								className="rounded-md"
								alt={`featured-image-${item.post_title}`}
							/>
						</div>
						<div className="col-span-2 col-end-4">
							<div className="flex space-x-3 items-center">
								<div className="flex space-x-2 col-start-1 col-end-3">
									{sticky && <Label name="sticky" />}
									<Link href={`/cate/${item.post_categories[0].term_id}`}>
										<a>
											<Label name="primary" icon="cate">
												{item.post_categories[0].name}
											</Label>
										</a>
									</Link>
								</div>
								<div
									className="justify-end hidden lg:flex lg:w-auto w-full"
									onClick={() => {
										setReader({ status: true, post: item })
									}}
								>
									<Label name="secondary" icon="preview">
										Preview
									</Label>
								</div>
							</div>
							<div className="lg:mt-4 mt-6">
								<Link href={`/post/${item.id}`}>
									<a>
										<h1 className="font-medium text-2 lg:text-listTitle text-gray-700 dark:text-white tracking-wider mb-5">
											{item.post_title}
										</h1>
									</a>
								</Link>
								<p
									className="text-gray-500 dark:text-gray-400 text-4 lg:text-3 tracking-wide leading-2 lg:leading-8 overflow-hidden overflow-ellipsis"
									dangerouslySetInnerHTML={{
										__html: DesSplit({ str: item.post_excerpt.four, n: 150 }),
									}}
								/>
							</div>
						</div>
					</div>
					<BottomCard item={item} />
				</div>
			)
		}
	} else {
		return (
			<CardWithImageTool item={item} sticky={sticky} setReader={setReader} />
		)
	}
}
