'use client'

import * as NProgress from 'nprogress'
import * as PropTypes from 'prop-types'
import { useEffect, memo } from 'react'

export interface ProgressBarProps {
	/**
	 * The color of the bar.
	 * @default "#29D"
	 */
	color?: string
	/**
	 * The start position of the bar.
	 * @default 0.3
	 */
	startPosition?: number
	/**
	 * The stop delay in milliseconds.
	 * @default 200
	 */
	stopDelayMs?: number
	/**
	 * The height of the bar.
	 * @default 3
	 */
	height?: number
	/**
	 * The other NProgress configuration options to pass to NProgress.
	 * @default null
	 */
	options?: Partial<NProgress.NProgressOptions>
	/**
	 * The nonce attribute to use for the `style` tag.
	 * @default undefined
	 */
	nonce?: string

	/**
	 * Use your custom CSS tag instead of the default one.
	 * This is useful if you want to use a different style or minify the CSS.
	 * @default (css) => <style nonce={nonce}>{css}</style>
	 */
	transformCSS?: (css: string) => JSX.Element
}

const ProgressBar = ({
	color = '#29D',
	startPosition = 0.3,
	stopDelayMs = 200,
	height = 3,
	options,
	nonce,
	transformCSS = (css) => <style nonce={nonce}>{css}</style>,
}: ProgressBarProps) => {
	let timer: NodeJS.Timeout | null = null

	useEffect(() => {
		if (options) {
			NProgress.configure(options)
		}
		const { navigation } = window
		if (navigation) {
			navigation.addEventListener('navigate', routeChangeStart)
			navigation.addEventListener('navigateerror', routeChangeError)
			navigation.addEventListener('navigatesuccess', routeChangeEnd)
			return () => {
				navigation.removeEventListener('navigate', routeChangeStart)
				navigation.removeEventListener('navigateerror', routeChangeError)
				navigation.removeEventListener('navigatesuccess', routeChangeEnd)
			}
		}
	}, [])

	const routeChangeStart = () => {
		NProgress.set(startPosition)
		NProgress.start()
	}

	const routeChangeEnd = () => {
		if (timer) clearTimeout(timer)
		timer = setTimeout(() => {
			NProgress.done(true)
		}, stopDelayMs)
	}

	const routeChangeError = () => {
		if (timer) clearTimeout(timer)
		timer = setTimeout(() => {
			NProgress.done(true)
		}, stopDelayMs)
	}

	return transformCSS(`
     #nprogress {
       pointer-events: none;
     }
     #nprogress .bar {
       background: ${color};
       position: fixed;
       z-index: 9999;
       top: 0;
       left: 0;
       width: 100%;
       height: ${height}px;
     }
     #nprogress .peg {
       display: block;
       position: absolute;
       right: 0px;
       width: 100px;
       height: 100%;
       box-shadow: 0 0 10px ${color}, 0 0 5px ${color};
       opacity: 1;
       -webkit-transform: rotate(3deg) translate(0px, -4px);
       -ms-transform: rotate(3deg) translate(0px, -4px);
       transform: rotate(3deg) translate(0px, -4px);
     }
     #nprogress .spinner {
       display: block;
       position: fixed;
       z-index: 1031;
       top: 15px;
       right: 15px;
     }
     #nprogress .spinner-icon {
       width: 18px;
       height: 18px;
       box-sizing: border-box;
       border: solid 2px transparent;
       border-top-color: ${color};
       border-left-color: ${color};
       border-radius: 50%;
       -webkit-animation: nprogresss-spinner 400ms linear infinite;
       animation: nprogress-spinner 400ms linear infinite;
     }
     .nprogress-custom-parent {
       overflow: hidden;
       position: relative;
     }
     .nprogress-custom-parent #nprogress .spinner,
     .nprogress-custom-parent #nprogress .bar {
       position: absolute;
     }
     @-webkit-keyframes nprogress-spinner {
       0% {
         -webkit-transform: rotate(0deg);
       }
       100% {
         -webkit-transform: rotate(360deg);
       }
     }
     @keyframes nprogress-spinner {
       0% {
         transform: rotate(0deg);
       }
       100% {
         transform: rotate(360deg);
       }
     }
   `)
}

ProgressBar.propTypes = {
	color: PropTypes.string,
	startPosition: PropTypes.number,
	stopDelayMs: PropTypes.number,
	height: PropTypes.number,
	showOnShallow: PropTypes.bool,
	options: PropTypes.object,
	nonce: PropTypes.string,
	transformCSS: PropTypes.func,
}

export default memo(ProgressBar)
