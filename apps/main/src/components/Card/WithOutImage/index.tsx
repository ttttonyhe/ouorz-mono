import { Label } from "@twilight-toolkit/ui"
import Link from "next/link"
import CardFooter from "~/components/Card/Footer"
import { WPPost } from "~/constants/propTypes"
import { useDispatch } from "~/hooks"
import { setReaderRequest } from "~/store/reader/actions"
import { trimStr } from "~/utilities/string"

interface Props {
	item: WPPost
	sticky: boolean
}

export default function CardWithOutImage({ item, sticky }: Props) {
	const dispatch = useDispatch()
	return (
		<div className="mb-6 w-full rounded-md border bg-white shadow-xs dark:border-gray-700 dark:bg-gray-800">
			<div className="p-5 lg:p-10">
				<div className="col-span-2 col-end-4">
					<div className="grid grid-cols-4 items-center">
						<div className="col-start-1 col-end-3 flex space-x-2">
							{sticky && <Label type="sticky-icon" />}
							<Link href={`/cate/${item.post_categories[0].term_id}`}>
								<Label type="primary" icon="cate">
									{item.post_categories[0].name}
								</Label>
							</Link>
						</div>
						<div
							data-oa="click-previewPost"
							className="col-start-4 col-end-5 hidden justify-end lg:flex"
							onClick={() => {
								dispatch(setReaderRequest(item))
							}}>
							<Label type="secondary" icon="preview">
								Preview
							</Label>
						</div>
					</div>
					<div className="mt-6">
						<Link href={`/post/${item.id}`}>
							<h1 className="text-2 lg:text-list-title mb-5 font-medium tracking-wider text-gray-700 dark:text-white">
								{item.post_title}
							</h1>
						</Link>
						<p
							className="text-4 lg:text-3 overflow-hidden leading-2 tracking-wide text-ellipsis text-gray-500 lg:leading-8 dark:text-gray-400"
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
