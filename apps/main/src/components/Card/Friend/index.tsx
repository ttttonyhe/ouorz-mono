import Image from "next/image"
import Link from "next/link"
import type { WPPost } from "~/constants/propTypes"
import { trimStr } from "~/utilities/string"

interface Props {
	item: WPPost
}

export default function CardFriend({ item }: Props) {
	return (
		<div className="mb-6 w-full rounded-md border bg-white shadow-xs">
			<div className="flex">
				<Image
					src={item.post_img.url}
					width={50}
					height={50}
					className="border shadow-xs"
					alt={`${item.post_title} site image`}
					loading="lazy"
				/>
				<div>
					<Link href={`/post/${item.id}`}>
						<h1 className="mb-1 font-medium text-2 text-gray-700 tracking-wider">
							{item.post_title}
						</h1>
					</Link>
					<p
						className="text-3 text-gray-500 leading-8 tracking-wide"
						dangerouslySetInnerHTML={{
							__html: trimStr(item.post_excerpt.four, 150),
						}}
					/>
				</div>
			</div>
		</div>
	)
}
