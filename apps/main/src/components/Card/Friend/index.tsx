import Link from 'next/link'
import trimStr from '~/utilities/trimString'
import Image from 'next/image'

interface Props {
	item: WPPost
}

export default function CardFriend({ item }: Props) {
	return (
		<div className="w-full shadow-sm bg-white rounded-md border mb-6">
			<div className="flex">
				<Image
					src={item.post_img.url}
					width={50}
					height={50}
					className="border shadow-sm"
					alt={`${item.post_title} site image`}
					loading="lazy"
				/>
				<div>
					<Link href={`/post/${item.id}`}>
						<h1 className="font-medium text-2 text-gray-700 tracking-wider mb-1">
							{item.post_title}
						</h1>
					</Link>
					<p
						className="text-gray-500 text-3 tracking-wide leading-8"
						dangerouslySetInnerHTML={{
							__html: trimStr(item.post_excerpt.four, 150),
						}}
					/>
				</div>
			</div>
		</div>
	)
}
