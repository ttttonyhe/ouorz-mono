import { useRouter } from 'next/router'
import Image from 'next/image'
import { GlowingBackground } from '~/components/Visual'
import openLink from '~/utilities/externalLink'
import { Icon } from '@twilight-toolkit/ui'

interface Props {
	title: string
	des: string
	icon?: string
	iconSmall?: string
	className?: string
	href?: string
	wrappable?: boolean
}

export default function PageCard({
	title,
	des,
	icon,
	iconSmall,
	className,
	href,
	wrappable,
}: Props) {
	const router = useRouter()
	const handleClick = () => {
		if (href) {
			if (href.indexOf('http') === -1) {
				router.push(href)
			} else {
				openLink(href)
			}
		}
	}
	return (
		<div
			className="glowing-div cursor-pointer hover:shadow-md transition-shadow shadow-sm border dark:border-0 pt-3 pb-4 px-4 bg-white dark:bg-gray-700 flex items-center rounded-md"
			onClick={handleClick}
		>
			<GlowingBackground />
			<div className="glowing-div-content overflow-hidden flex items-center">
				{icon && (
					<div
						className={`lg:flex justify-center items-center hidden w-20 h-auto border-r border-r-gray-200 dark:border-r-gray-600 pr-3 mr-4 ${
							className ? className : ''
						}`}
					>
						{icon.indexOf('://') > -1 ? (
							<Image
								src={icon}
								width={35}
								height={35}
								alt={`remote image ${icon}`}
							/>
						) : (
							<Icon name={icon} />
						)}
					</div>
				)}
				<div className="w-full">
					<h1
						className={`flex items-center text-2xl tracking-wide font-medium ${
							iconSmall || wrappable ? '' : '-mb-1'
						}`}
					>
						{iconSmall && (
							<span
								className={`lg:block hidden w-7 h-7 mr-1.5 ${
									className ? className : ''
								}`}
							>
								<Icon name={iconSmall} />
							</span>
						)}
						{title}
					</h1>
					<p
						className={`text-4 text-gray-600 dark:text-gray-400 tracking-wide ${
							wrappable
								? 'mt-1 overflow-wrap-breakword leading-tight'
								: 'whitespace-nowrap'
						} overflow-hidden text-ellipsis`}
					>
						{des}
					</p>
				</div>
			</div>
		</div>
	)
}
