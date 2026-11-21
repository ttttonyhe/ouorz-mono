import { Icon } from "@twilight-toolkit/ui"
import { useTheme } from "next-themes"
import { useRouter } from "next/router"
import { useEffect, useRef } from "react"
import useMounted from "~/hooks/useMounted"
import { deactivateKbar, useAppState } from "~/store"

import { OffsetTransition } from "../Motion"

const themes = ["system", "dark", "light"] as const
const icons = [
	<Icon key="system" name="gear" />,
	<Icon key="dark" name="moon" />,
	<Icon key="light" name="sun" />,
] as const
const targetThemes = ["dark", "light", "system"] as const

export default function Footer() {
	const visible = useAppState((s) => s.kbar.visible)
	const { setTheme, theme, resolvedTheme } = useTheme()
	const { asPath } = useRouter()
	const mounted = useMounted()
	const backToTopRef = useRef<HTMLButtonElement>(null)
	const previousPathRef = useRef(asPath)
	const rawIndex = themes.indexOf(
		theme === "dark" || theme === "light" ? theme : "system"
	)
	const themeIndex = mounted && rawIndex >= 0 ? rawIndex : 0

	useEffect(() => {
		if (!mounted || resolvedTheme !== "dark") return

		let rafId: number | null = null
		let lastEvent: PointerEvent | null = null

		const updateGlow = () => {
			rafId = null
			const ev = lastEvent
			if (!ev) return
			const nodes = document.querySelectorAll<HTMLElement>(".glowing-div")
			if (nodes.length === 0) return

			for (const featureEl of nodes) {
				const rect = featureEl.getBoundingClientRect()
				featureEl.style.setProperty("--x", `${ev.clientX - rect.left}`)
				featureEl.style.setProperty("--y", `${ev.clientY - rect.top}`)
			}
		}

		// Document-level listener survives route changes replacing `.glowing-area`.
		const handler = (ev: PointerEvent) => {
			lastEvent = ev
			if (rafId === null) rafId = window.requestAnimationFrame(updateGlow)
		}

		document.addEventListener("pointermove", handler, { passive: true })

		return () => {
			document.removeEventListener("pointermove", handler)
			if (rafId !== null) window.cancelAnimationFrame(rafId)
		}
	}, [mounted, resolvedTheme])

	useEffect(() => {
		if (previousPathRef.current === asPath) return

		previousPathRef.current = asPath
		if (visible) deactivateKbar()
	}, [asPath, visible])

	const nextTheme = targetThemes[themeIndex] ?? "dark"

	return (
		<footer
			lang="en"
			className="mt-20 border-t border-b border-gray-200 bg-white py-4 text-center dark:border-gray-700 dark:bg-gray-800">
			<div className="fixed bottom-8 left-8 text-gray-500 dark:text-gray-300">
				<button
					type="button"
					aria-label="change theme"
					onClick={() => {
						setTheme(nextTheme)
					}}
					className="effect-pressing flex w-full cursor-pointer items-center justify-center rounded-md border border-gray-300 bg-white p-3! text-xl tracking-wider shadow-xs hover:shadow-inner focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
					<span className="h-7 w-7">{icons[themeIndex]}</span>
				</button>
			</div>
			<div className="fixed right-8 bottom-8 text-gray-500 dark:text-gray-300">
				<OffsetTransition componentRef={backToTopRef}>
					<button
						type="button"
						ref={backToTopRef}
						aria-label="Back to top"
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
					rel="noopener noreferrer">
					CC BY-NC-SA 4.0
				</a>{" "}
				<span>·</span>{" "}
				<a
					href="https://github.com/ttttonyhe/ouorz-mono"
					target="_blank"
					rel="noopener noreferrer">
					Open Source Software (OSS)
				</a>{" "}
				<span>·</span>{" "}
				<span className="inline-flex items-center gap-1">
					<a
						href="https://cs.uwatering.com/#https://lipeng.ac/?nav=prev"
						aria-label="Previous site in the Waterloo Computer Science webring"
						rel="noopener noreferrer"
						target="_blank">
						←
					</a>
					<a
						href="https://cs.uwatering.com/#https://lipeng.ac/"
						aria-label="Waterloo Computer Science webring"
						target="_blank"
						rel="noopener noreferrer"
						className="pt-0.5">
						<Icon name="uwCSWebring" />
					</a>
					<a
						href="https://cs.uwatering.com/#https://lipeng.ac/?nav=next"
						aria-label="Next site in the Waterloo Computer Science webring"
						rel="noopener noreferrer"
						target="_blank">
						→
					</a>
				</span>
			</p>
		</footer>
	)
}
