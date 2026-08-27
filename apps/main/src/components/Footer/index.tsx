import { Icon } from "@twilight-toolkit/ui"
import { useTheme } from "next-themes"
import { useRouter } from "next/router"
import { useEffect, useRef } from "react"
import smoothScroll from "smoothscroll-polyfill"

import { useDispatch, useSelector } from "~/hooks"
import useMounted from "~/hooks/useMounted"
import { deactivateKbar } from "~/store/kbar/actions"
import { selectKbar } from "~/store/kbar/selectors"

import { OffsetTransition } from "../Motion"

const themes = ["system", "dark", "light"]
const icons = [
	<Icon key="system" name="gear" />,
	<Icon key="dark" name="moon" />,
	<Icon key="light" name="sun" />,
]
const targetThemes = ["dark", "light", "system"]

export default function Footer() {
	const dispatch = useDispatch()
	const { visible } = useSelector(selectKbar)
	const { setTheme, theme, resolvedTheme } = useTheme()
	const { pathname } = useRouter()
	const mounted = useMounted()
	const backToTopRef = useRef<HTMLButtonElement>(null)
	const previousPathnameRef = useRef(pathname)

	useEffect(() => {
		smoothScroll.polyfill()
	}, [])

	useEffect(() => {
		if (!mounted || resolvedTheme !== "dark") return

		// Listening on the document keeps the glow working across route changes,
		// where the previous page's `.glowing-area` node is replaced.
		const handler = (ev: PointerEvent) => {
			for (const featureEl of document.querySelectorAll<HTMLElement>(
				".glowing-div"
			)) {
				const rect = featureEl.getBoundingClientRect()
				featureEl.style.setProperty("--x", `${ev.clientX - rect.left}`)
				featureEl.style.setProperty("--y", `${ev.clientY - rect.top}`)
			}
		}

		document.addEventListener("pointermove", handler)

		return () => {
			document.removeEventListener("pointermove", handler)
		}
	}, [mounted, resolvedTheme])

	useEffect(() => {
		if (previousPathnameRef.current === pathname) return

		previousPathnameRef.current = pathname
		if (visible) dispatch(deactivateKbar())
	}, [dispatch, pathname, visible])

	if (!mounted) return null

	return (
		<footer className="mt-20 border-t border-b border-gray-200 bg-white py-4 text-center dark:border-gray-700 dark:bg-gray-800">
			<div className="fixed bottom-8 left-8 text-gray-500 dark:text-gray-300">
				<button
					aria-label="change theme"
					onClick={() => {
						setTheme(targetThemes[themes.indexOf(theme)])
					}}
					className="effect-pressing flex w-full cursor-pointer items-center justify-center rounded-md border border-gray-300 bg-white p-3! text-xl tracking-wider shadow-xs hover:shadow-inner focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
					<span className="h-7 w-7">{icons[themes.indexOf(theme)]}</span>
				</button>
			</div>
			<div className="fixed right-8 bottom-8 text-gray-500 dark:text-gray-300">
				<OffsetTransition componentRef={backToTopRef}>
					<button
						ref={backToTopRef}
						aria-label="change theme"
						onClick={() => {
							window.scrollTo({ top: 0, behavior: "smooth" })
						}}
						className="effect-pressing flex w-full cursor-pointer items-center justify-center rounded-md border border-gray-300 bg-white p-3 text-xl tracking-wider opacity-0 shadow-xs hover:shadow-inner focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
						<span className="h-7 w-7">
							<Icon name="arrowUp" />
						</span>
					</button>
				</OffsetTransition>
			</div>
			<p className="text-4 tracking-wide text-gray-500 dark:text-gray-400">
				<a
					href="https://creativecommons.org/licenses/by-nc-sa/4.0/"
					target="_blank"
					rel="noreferrer">
					CC BY-NC-SA 4.0
				</a>{" "}
				<span>·</span>{" "}
				<a
					href="https://github.com/ttttonyhe/ouorz-mono"
					target="_blank"
					rel="noreferrer">
					Open Source Software (OSS)
				</a>{" "}
				<span>·</span>{" "}
				<span className="inline-flex items-center gap-1">
					<a
						href="https://cs.uwatering.com/#https://lipeng.ac/?nav=prev"
						rel="noreferrer"
						target="_blank">
						←
					</a>
					<a
						href="https://cs.uwatering.com/#https://lipeng.ac/"
						target="_blank"
						rel="noreferrer"
						className="pt-0.5">
						<Icon name="uwCSWebring" />
					</a>
					<a
						href="https://cs.uwatering.com/#https://lipeng.ac/?nav=next"
						rel="noreferrer"
						target="_blank">
						→
					</a>
				</span>
			</p>
		</footer>
	)
}
