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

export const CardTool = ({
	item,
	preview,
}: {
	item: WPPost
	preview: boolean
}) => {
	const dispatch = useDispatch()
	return (
		<div className="w-full overflow-hidden whitespace-nowrap rounded-md border border-gray-200 shadow-sm dark:border-gray-600 dark:bg-gray-600 lg:grid lg:grid-cols-8 lg:gap-3">
			<div
				className={`col-start-1 col-end-2 rounded-bl-md rounded-tl-md ${
					item.post_metas.fineTool.itemImgBorder
						? "border-r border-gray-200 dark:border-gray-600"
						: ""
				}`}
				style={{
					backgroundImage: "url(" + item.post_img.url + ")",
					backgroundSize: "cover",
					backgroundRepeat: "no-repeat",
					backgroundPosition: "center",
				}}
			/>
			<div className="col-start-2 col-end-9 grid grid-cols-2 items-center py-2 pl-3 pr-3 lg:pl-0">
				<div className="items-center justify-center">
					<h2 className="text-xl font-bold text-gray-600 dark:text-gray-200">
						{item.post_metas.fineTool.itemName}
					</h2>
					<p className="text-ellipsis text-5 text-gray-500 dark:text-gray-400">
						{item.post_metas.fineTool.itemDes}
					</p>
				</div>
				<div className="hidden justify-end space-x-2 lg:flex">
					{preview && (
						<Label
							type="gray-icon"
							icon="preview"
							data-oa="click-previewPost"
							onClick={() => {
								dispatch(setReaderRequest(item))
							}}
						/>
					)}
					<a
						href={item.post_metas.fineTool.itemLink}
						target="_blank"
						rel="noreferrer"
						data-oa="click-visitTool">
						<Label type="green" icon="right" preview={preview}>
							{item.post_metas.fineTool.itemLinkName}
						</Label>
					</a>
				</div>
			</div>
		</div>
	)
}

export default function CardWithImageTool({ item, sticky }: Props) {
	return (
		<div
			className={`w-full rounded-md border bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 ${
				sticky ? "mb-6 border-t-4 border-t-yellow-200" : "mb-6"
			}`}>
			<div className="p-5 lg:p-10">
				<CardTool item={item} preview={true} />
				<div className="mt-6">
					<Link href={`/post/${item.id}`}>
						<h1
							className="mb-5 text-2 font-bold tracking-wider text-gray-700 dark:text-white lg:text-listTitle"
							dangerouslySetInnerHTML={{ __html: item.post_title }}
						/>
					</Link>
					<p
						className="leading-2 overflow-hidden text-ellipsis text-4 tracking-wide text-gray-500 dark:text-gray-400 lg:text-3 lg:leading-8"
						dangerouslySetInnerHTML={{
							__html: trimStr(item.post_excerpt.four, 150),
						}}
					/>
				</div>
			</div>
			<CardFooter item={item} />
		</div>
	)
}
