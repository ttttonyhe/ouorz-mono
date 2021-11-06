import Icons from '~/components/Icons'
import { useRouter } from 'next/router'

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
			className="cursor-pointer hover:shadow-md transition-shadow shadow-sm border py-3 px-4 bg-white dark:bg-gray-800 dark:border-gray-800 flex items-center rounded-md"
			onClick={handleClick}
		>
			{icon && (
				<div
					className={`lg:block hidden w-20 h-auto border-r border-r-gray-200 dark:border-r-gray-600 pr-3 mr-3 ${
						className ? className : ''
					}`}
				>
					{Icons[icon]}
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
							{Icons[iconSmall]}
						</span>
					)}
					{title}
				</h1>
				<p className="text-4 text-gray-600 dark:text-gray-400 tracking-wide whitespace-nowrap overflow-hidden overflow-ellipsis">
					{des}
				</p>
			</div>
		</div>
	)
}
