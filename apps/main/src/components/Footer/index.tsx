import { useTheme } from 'next-themes'
import { useEffect, useState, useRef } from 'react'
import { useRouter } from 'next/router'
import { Button, Icon } from '@twilight-toolkit/ui'
import { useDispatch, useSelector } from '~/hooks'
import { deactivateKbar } from '~/store/kbar/actions'
import { selectKbar } from '~/store/kbar/selectors'
import smoothScroll from 'smoothscroll-polyfill'
import { OffsetTransition } from '../Motion'

const themes = ['system', 'dark', 'light']
const icons = [
	<Icon key="system" name="gear" />,
	<Icon key="dark" name="moon" />,
	<Icon key="light" name="sun" />,
]
const targetThemes = ['dark', 'light', 'system']

export default function Footer() {
	const dispatch = useDispatch()
	const { visible } = useSelector(selectKbar)
	const { setTheme, theme, resolvedTheme } = useTheme()
	const { pathname } = useRouter()
	const [mounted, setMounted] = useState(false)
	const backToTopRef = useRef<HTMLButtonElement>(null)

	useEffect(() => {
		smoothScroll.polyfill()
		setMounted(true)
	}, [])

	useEffect(() => {
		// Cursor glowing effect
		if (resolvedTheme === 'dark') {
			const glowingArea = document.querySelector('.glowing-area')
			const glowingDivs = document.querySelectorAll('.glowing-div')

			if (glowingArea) {
				const handler = (ev: any) => {
					glowingDivs.forEach((featureEl: any) => {
						const rect = featureEl.getBoundingClientRect()
						featureEl.style.setProperty('--x', ev.clientX - rect.left)
						featureEl.style.setProperty('--y', ev.clientY - rect.top)
					})
				}
				glowingArea.addEventListener('pointermove', handler)

				return () => {
					glowingArea.removeEventListener('pointermove', handler)
				}
			}
		}
		// Hide kbar on route change
		visible && dispatch(deactivateKbar())
	}, [pathname, resolvedTheme])

	if (!mounted) return null

	return (
		<footer className="border-gray-200 dark:border-gray-700 dark:bg-gray-800 border-t border-b text-center py-4 bg-white">
			<div className="fixed bottom-8 left-8 text-gray-500 dark:text-gray-300">
				<Button
					aria-label="change theme"
					onClick={() => {
						setTheme(targetThemes[themes.indexOf(theme)])
					}}
					className="w-full !p-3 shadow-sm border border-gray-300 dark:border-gray-800 hover:shadow-inner dark:hover:bg-gray-700 rounded-md cursor-pointer focus:outline-none justify-center items-center text-xl tracking-wider bg-white dark:bg-gray-800 flex"
				>
					<span className="w-7 h-7">{icons[themes.indexOf(theme)]}</span>
				</Button>
			</div>
			<div className="fixed bottom-8 right-8 text-gray-500 dark:text-gray-300">
				<OffsetTransition componentRef={backToTopRef}>
					<button
						ref={backToTopRef}
						aria-label="change theme"
						onClick={() => {
							window.scrollTo({ top: 0, behavior: 'smooth' })
						}}
						className="effect-pressing w-full p-3 shadow-sm border border-gray-300 dark:border-gray-800 hover:shadow-inner dark:hover:bg-gray-700 rounded-md cursor-pointer focus:outline-none justify-center items-center text-xl tracking-wider bg-white dark:bg-gray-800 flex"
					>
						<span className="w-7 h-7">
							<Icon name="arrowUp" />
						</span>
					</button>
				</OffsetTransition>
			</div>
			<p className="text-gray-500 text-4 tracking-wide dark:text-gray-400">
				<a
					href="https://twitter.com/ttttonyhe"
					target="_blank"
					rel="noreferrer"
				>
					@ttttonyhe
				</a>{' '}
				<span>·</span>{' '}
				<a
					href="https://creativecommons.org/licenses/by-nc-sa/4.0/"
					target="_blank"
					rel="noreferrer"
				>
					CC BY-NC-SA 4.0
				</a>{' '}
				<span>·</span>{' '}
				<a
					href="https://github.com/HelipengTony/ouorz-mono"
					target="_blank"
					rel="noreferrer"
				>
					OSS
				</a>
			</p>
		</footer>
	)
}
