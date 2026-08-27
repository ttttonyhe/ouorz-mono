import { Icon } from "@twilight-toolkit/ui"
import Image from "next/image"
import { useRouter } from "next/router"

import { GlowingBackground } from "~/components/Visual"
import openLink from "~/utilities/externalLink"
import { navigateWithTransition } from "~/utilities/viewTransition"

interface Props {
	title: string
	des: string
	icon?: string
	iconSmall?: string
	className?: string
	href?: string
	wrappable?: boolean
	viewTransitionName?: string
}

export default function PageCard({
	title,
	des,
	icon,
	iconSmall,
	className,
	href,
	wrappable,
	viewTransitionName,
}: Props) {
	const router = useRouter()
	const handleClick = () => {
		if (href) {
			const isExternalHref =
				href.startsWith("http://") || href.startsWith("https://")

			if (!isExternalHref) {
				if (viewTransitionName) {
					navigateWithTransition(router, href)
				} else {
					void router.push(href)
				}
			} else {
				openLink(href)
			}
		}
	}
	return (
		<div
			className="glowing-div flex cursor-pointer items-center rounded-md border bg-white px-4 pt-3 pb-4 shadow-xs transition-shadow hover:shadow-md dark:border-0 dark:bg-gray-700"
			onClick={handleClick}>
			<GlowingBackground />
			<div className="glowing-div-content flex items-center overflow-hidden">
				{icon && (
					<div
						className={`mr-4 hidden h-auto w-20 items-center justify-center border-r border-r-gray-200 pr-3 lg:flex dark:border-r-gray-600 ${
							className ? className : ""
						}`}>
						{icon.includes("://") ? (
							<Image
								src={icon}
								width={35}
								height={35}
								alt={`remote image ${icon}`}
								loading="lazy"
								className="h-[35px] w-[35px] object-contain"
							/>
						) : // oxlint-disable-next-line eslint/no-control-regex -- the range detects non-ASCII glyph icons
						/[^\u0000-\u007F]/u.test(icon) ? (
							<span className="flex h-[35px] w-[35px] items-center justify-center text-[32px] leading-none">
								{icon}
							</span>
						) : (
							<Icon name={icon} />
						)}
					</div>
				)}
				<div className="w-full">
					<h1
						className={`flex items-center text-2xl font-medium tracking-wide ${
							iconSmall || wrappable ? "" : "-mb-1"
						}`}>
						{iconSmall && (
							<span
								className={`mr-1.5 hidden h-7 w-7 lg:block ${
									className ? className : ""
								}`}>
								<Icon name={iconSmall} />
							</span>
						)}
						<span
							style={viewTransitionName ? { viewTransitionName } : undefined}>
							{title}
						</span>
					</h1>
					<p
						className={`text-4 tracking-wide text-gray-600 dark:text-gray-400 ${
							wrappable
								? "overflow-wrap-breakword mt-1 leading-tight"
								: "whitespace-nowrap"
						} overflow-hidden text-ellipsis`}>
						{des}
					</p>
				</div>
			</div>
		</div>
	)
}
