import { useRouter } from 'next/router'
import Image from 'next/image'
import { GlowingBackground } from '~/components/Visual'
import { Icon } from '@twilight-toolkit/ui'

interface Props {
	title: string
	des: string
	icon?: string
	iconSmall?: string
	className?: string
	href?: string
}

export default function PageCard({
	title,
	des,
	icon,
	iconSmall,
	className,
	href,
}: Props) {
	const router = useRouter()
	const handleClick = () => {
		if (href) {
			if (href.indexOf('http') === -1) {
				router.push(href)
			} else {
				window.location.href = href
			}
		}
	}
	return (
		<div
			className="glowing-div cursor-pointer hover:shadow-md transition-shadow shadow-sm border dark:border-0 py-3 px-4 bg-white dark:bg-gray-700 dark:border-gray-700 flex items-center rounded-md"
			onClick={handleClick}
		>
			<GlowingBackground />
			<div className="glowing-div-content flex items-center">
				{icon && (
					<div
						className={`lg:flex justify-center items-center hidden w-20 h-auto border-r border-r-gray-200 dark:border-r-gray-600 pr-3 mr-4 ${
							className ? className : ''
						}`}
					>
						{icon.indexOf('://') > -1 ? (
							<Image src={icon} width={35} height={35} />
						) : (
							<Icon name={icon} />
						)}
					</div>
				)}
				<div className="w-full">
					<h1
						className={`flex items-center text-2xl tracking-wide font-medium ${
							iconSmall ? '' : '-mb-1'
						}`}
					>
						{iconSmall && (
							<span
								className={`lg:block hidden w-7 h-7 mr-1 ${
									className ? className : ''
								}`}
							>
								<Icon name={iconSmall} />
							</span>
						)}
						{title}
					</h1>
					<p className="text-4 text-gray-600 dark:text-gray-400 tracking-wide whitespace-nowrap overflow-hidden text-ellipsis">
						{des}
					</p>
				</div>
			</div>
		</div>
	)
}
