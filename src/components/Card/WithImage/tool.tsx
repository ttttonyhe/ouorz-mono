import Label from '~/components/Label'
import BottomCard from '~/components/Card/Bottom'
import { DesSplit } from '~/assets/utilities/String'
import Link from 'next/link'

interface Props {
	item: any
	sticky: boolean
	setReader?: any
}

export const CardTool = ({
	item,
	preview,
	setReader,
}: {
	item: any
	preview: boolean
	setReader?: any
}) => {
	return (
		<div className="w-full whitespace-nowrap lg:grid lg:grid-cols-8 lg:gap-3 rounded-md shadow-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-600 overflow-hidden">
			<div
				className={`col-start-1 col-end-2 rounded-tl-md rounded-bl-md ${
					item.post_metas.fineTool.itemImgBorder
						? 'border-r border-gray-200 dark:border-gray-600'
						: ''
				}`}
				style={{
					backgroundImage: 'url(' + item.post_img.url + ')',
					backgroundSize: 'cover',
					backgroundRepeat: 'no-repeat',
					backgroundPosition: 'center',
				}}
			/>
			<div className="col-start-2 col-end-9 grid grid-cols-2 items-center pl-3 lg:pl-0 py-2 pr-3">
				<div className="justify-center items-center">
					<h2 className="text-xl font-medium text-gray-600 dark:text-gray-200">
						{item.post_metas.fineTool.itemName}
					</h2>
					<p className="text-gray-500 dark:text-gray-400 overflow-ellipsis text-5">
						{item.post_metas.fineTool.itemDes}
					</p>
				</div>
				<div className="hidden lg:flex justify-end space-x-2">
					{preview && (
						<a
							onClick={() => {
								setReader({ status: true, post: item })
							}}
						>
							<Label name="gray" icon="preview" />
						</a>
					)}
					<a
						href={item.post_metas.fineTool.itemLink}
						target="_blank"
						rel="noreferrer"
					>
						<Label name="green" icon="right" preview={preview}>
							{item.post_metas.fineTool.itemLinkName}
						</Label>
					</a>
				</div>
			</div>
		</div>
	)
}

export default function CardWithImageTool({ item, sticky, setReader }: Props) {
	return (
		<div
			className={`w-full shadow-sm bg-white dark:bg-gray-800 dark:border-gray-800 rounded-md border ${
				sticky ? 'border-t-4 border-t-yellow-200 mb-6' : 'mb-6'
			}`}
		>
			<div className="p-5 lg:p-10">
				<CardTool item={item} preview={true} setReader={setReader} />
				<div className="mt-6">
					<Link href={`/post/${item.id}`}>
						<a>
							<h1
								className="font-medium text-2 lg:text-listTitle text-gray-700 dark:text-white tracking-wider mb-5"
								dangerouslySetInnerHTML={{ __html: item.post_title }}
							/>
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
			<BottomCard item={item} />
		</div>
	)
}
