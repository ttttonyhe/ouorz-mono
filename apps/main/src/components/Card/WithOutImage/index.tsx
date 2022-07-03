import { Label } from '@twilight-toolkit/ui'
import CardFooter from '~/components/Card/Footer'
import trimStr from '~/utilities/trimString'
import Link from 'next/link'
import { useDispatch } from '~/hooks'
import { setReaderRequest } from '~/store/reader/actions'
import { WPPost } from '~/constants/propTypes'

interface Props {
	item: WPPost
	sticky: boolean
}

export default function CardWithOutImage({ item, sticky }: Props) {
	const dispatch = useDispatch()
	return (
		<div className="w-full shadow-sm bg-white dark:bg-gray-800 dark:border-gray-800 rounded-md border mb-6">
			<div className="p-5 lg:p-10">
				<div className="col-span-2 col-end-4">
					<div className="grid grid-cols-4 items-center">
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
							data-oa="click-previewPost"
							className="col-start-4 col-end-5 justify-end hidden lg:flex"
							onClick={() => {
								dispatch(setReaderRequest(item))
							}}
						>
							<Label type="secondary" icon="preview">
								Preview
							</Label>
						</div>
					</div>
					<div className="mt-6">
						<Link href={`/post/${item.id}`}>
							<a>
								<h1 className="font-medium text-2 lg:text-listTitle text-gray-700 dark:text-white tracking-wider mb-5">
									{item.post_title}
								</h1>
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
