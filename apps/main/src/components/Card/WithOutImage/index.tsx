import { Label } from "@twilight-toolkit/ui"
import Link from "next/link"
import CardFooter from "~/components/Card/Footer"
import type { WPPost } from "~/constants/propTypes"
import { openReader } from "~/store"
import { trimStr } from "~/utilities/string"

interface Props {
	item: WPPost
	sticky: boolean
}

export default function CardWithOutImage({ item, sticky }: Props) {
	const primaryCategory = item.post_categories[0]

	return (
		<div className="mb-6 w-full rounded-md border bg-white shadow-xs dark:border-gray-700 dark:bg-gray-800">
			<div className="p-5 lg:p-10">
				<div className="col-span-2 col-end-4">
					<div className="grid grid-cols-4 items-center">
						<div className="col-start-1 col-end-3 flex space-x-2">
							{sticky && <Label type="sticky-icon" />}
							{primaryCategory && (
								<Link href={`/cate/${primaryCategory.term_id}`}>
									<Label type="primary" icon="cate">
										{primaryCategory.name}
									</Label>
								</Link>
							)}
						</div>
						<div className="col-start-4 col-end-5 hidden justify-end lg:flex">
							<button
								type="button"
								data-oa="click-previewPost"
								onClick={() => openReader(item)}>
								<Label type="secondary" icon="preview">
									Preview
								</Label>
							</button>
						</div>
					</div>
					<div className="mt-4">
						<Link href={`/post/${item.id}`}>
							<h2 className="mb-2.5 text-2 font-medium tracking-wider text-gray-700 lg:text-list-title dark:text-white">
								{item.post_title}
							</h2>
						</Link>
						<p
							className="overflow-hidden text-4 leading-2 tracking-wide text-ellipsis text-gray-500 lg:text-3 lg:leading-8 dark:text-gray-400"
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
