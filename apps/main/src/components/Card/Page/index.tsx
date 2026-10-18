import { Icon, type IconProps, isIconName } from "@twilight-toolkit/ui"
import Image from "next/image"
import Link from "next/link"
import { useRouter } from "next/router"

import { GlowingBackground } from "~/components/Visual"
import { navigateWithTransition } from "~/utilities/viewTransition"

interface Props {
	title: string
	des: string
	icon?: string
	iconSmall?: IconProps["name"]
	className?: string
	href?: string
	wrappable?: boolean
	viewTransitionName?: string
	heading?: "h2" | "h3"
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
	heading: Heading = "h2",
}: Props) {
	const router = useRouter()
	const cardClassName =
		"glowing-div flex cursor-pointer items-center rounded-md border bg-white px-4 pt-3 pb-4 shadow-xs transition-shadow hover:shadow-md dark:border-0 dark:bg-gray-700"
	const isExternalHref =
		href?.startsWith("https://") || href?.startsWith("http://")
	const content = (
		<>
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
								alt=""
								loading="lazy"
								className="h-[35px] w-[35px] object-contain"
							/>
						) : // oxlint-disable-next-line eslint/no-control-regex -- the range detects non-ASCII glyph icons
						/[^\u0000-\u007F]/u.test(icon) ? (
							<span className="flex h-[35px] w-[35px] items-center justify-center text-[32px] leading-none">
								{icon}
							</span>
						) : (
							isIconName(icon) && <Icon name={icon} />
						)}
					</div>
				)}
				<div className="w-full">
					<Heading
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
					</Heading>
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
		</>
	)

	if (!href) return <div className={cardClassName}>{content}</div>

	return (
		<Link
			href={href}
			className={cardClassName}
			target={isExternalHref ? "_blank" : undefined}
			rel={isExternalHref ? "noreferrer" : undefined}
			onNavigate={(event) => {
				if (viewTransitionName) {
					event.preventDefault()
					navigateWithTransition(router, href)
				}
			}}>
			{content}
		</Link>
	)
}
