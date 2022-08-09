/* eslint-disable @typescript-eslint/no-var-requires */
/* eslint-disable prettier/prettier */

var flattenColorPalette =
	require('tailwindcss/lib/util/flattenColorPalette').default
const colors = require('tailwindcss/colors')

module.exports = {
	content: ['./src/**/*.tsx', '../../packages/twilight-ui/**/*.tsx'],
	darkMode: 'class',
	theme: {
		extend: {
			spacing: {
				1: '3px',
				2: '6px',
				3: '9px',
				4: '12px',
				4.5: '1rem',
				6: '18px',
				7: '21px',
				9: '27px',
				10: '30px',
				20: '60px',
				readerOffset: '-47.5px',
				searchOffset: 'calc((100% - 680px) / 2)',
				aside: 'calc(100% - 6rem)',
			},
			colors: {
				gbg: '#f6f7f8',
				menu: '#ebeced',
				green: colors.emerald,
				yellow: colors.amber,
				purple: colors.violet,
				gray: colors.neutral,
			},
			boxShadow: {
				header: '0 4px 8px rgba(0,0,0,.04)',
			},
			width: {
				content: '680px',
				page: '720px',
				toc: '200px',
			},
			height: {
				img: '240px',
			},
			minHeight: {
				main: 'calc(100vh - 6.65rem)',
			},
			fontSize: {
				1: '32px',
				1.5: '23px',
				2: '18px',
				3: '15px',
				4: '14px',
				5: '12px',
				listTitle: '26px',
				label: '18px',
				postTitle: '30px',
				xl: '15px',
				'2xl': '18px',
				'3xl': '22.5px',
				stats: '28px',
			},
			lineHeight: {
				14: '1.4',
			},
			padding: {
				pre: '0.32rem',
			},
			margin: {
				'-82': '-220px',
			},
			animation: {
				pointer: 'pointer 1s infinite',
				reader: 'moveUp ease-in-out .5s',
				readerOut: 'moveDown ease-in-out .5s',
				readerBg: 'opacityProgressIn ease-in-out .5s',
				readerBgOut: 'opacityProgressOut ease-in-out .5s',
				searchBgOut: 'opacityProgressOut ease-in-out .15s forwards',
				search: '200ms ease 0s 1 normal none running search',
				searchOut: '150ms ease 0s 1 normal forwards running searchOut',
				kbar: '200ms ease 0.25s 1 normal forwards running kbar',
				kbarOut: '200ms ease 0s 1 normal forwards running kbarOut',
				kbarBg: '200ms ease 0s 1 normal none running opacityProgressIn',
				kbarBgOut: '200ms ease 0s 1 normal forwards running opacityProgressOut',
				kbarTransition: '200ms ease 0s 1 normal none running kbarTransition',
				kbarHighlighter:
					'100ms ease 0s 1 normal none running opacityProgressIn',
				waveHand: 'wavingHand ease-in-out 1.5s 3',
				waveHandAgain: 'wavingHand ease-in-out 1.5s infinite',
				completePulse: 'completePulse ease-in-out 2.5s infinite',
			},
			keyframes: {
				pointer: {
					'0%,100%': {
						transform: 'translateX(0)',
					},
					'50%': {
						transform: 'translateX(15%)',
					},
				},
				moveUp: {
					'0%': {
						transform: 'translateY(100vh)',
					},
					'80%': {
						transform: 'translateY(-5px)',
					},
					'100%': {
						transform: 'translateY(0vh)',
					},
				},
				moveDown: {
					'0%': {
						transform: 'translateY(0vh)',
					},
					'100%': {
						transform: 'translateY(100vh)',
					},
				},
				opacityProgressIn: {
					'0%': {
						opacity: '0',
					},
					'100%': {
						opacity: '1',
					},
				},
				opacityProgressOut: {
					'0%': {
						opacity: '1',
					},
					'100%': {
						opacity: '0',
					},
				},
				search: {
					'0%': {
						opacity: 0,
						transform: 'scale(0.9, 0.9)',
					},
					'100%': {
						opacity: 1,
						transform: 'scale(1.0, 1.0)',
					},
				},
				searchOut: {
					'0%': {
						opacity: 1,
						transform: 'scale(1.0, 1.0)',
					},
					'100%': {
						opacity: 0,
						transform: 'scale(0.9, 0.9)',
					},
				},
				kbar: {
					'0%': {
						opacity: 0,
						transform: 'scale(0.95, 0.95)',
					},
					'100%': {
						opacity: 1,
						transform: 'scale(1.0, 1.0)',
					},
				},
				kbarOut: {
					'0%': {
						opacity: 1,
						transform: 'scale(1.0, 1.0)',
					},
					'100%': {
						opacity: 0,
						transform: 'scale(0.95, 0.95)',
					},
				},
				kbarTransition: {
					'0%': {
						transform: 'scale(1)',
					},
					'50%': {
						transform: 'scale(0.98)',
					},
					'100%': {
						transform: 'scale(1)',
					},
				},
				wavingHand: {
					'0%': {
						transform: 'none',
						transformOrigin: '70% 70%',
					},
					'25%': {
						transform: 'rotate3d(1, 1, 1, -15deg)',
						transformOrigin: '70% 70%',
					},
					'50%': {
						transform: 'rotate3d(1, 1, 1, 15deg)',
						transformOrigin: '70% 70%',
					},
					'75%': {
						transform: 'rotate3d(1, 1, 1, -15deg)',
						transformOrigin: '70% 70%',
					},
					'100%': {
						transform: 'none',
						transformOrigin: '70% 70%',
					},
				},
				completePulse: {
					'0%': {
						opacity: 1,
					},
					'50%': {
						opacity: 0,
					},
					'100%': {
						opacity: 1,
					},
				},
			},
			transitionProperty: {
				width: 'width',
			},
			typography: {
				xl: {
					css: {
						fontSize: '16.2px',
						color: 'rgba(33,37,41,0.95)',
						a: {
							color: '#1e87f0',
							textDecoration: 'none',
							fontWeight: 'normal',
							'&:hover': {
								textDecoration: 'underline',
							},
						},
						blockquote: {
							fontWeight: '400',
							justifyItems: 'center',
						},
						h4: {
							fontSize: '1.3em',
						},
					},
				},
				dark: {
					css: {
						fontSize: '16.2px',
						a: {
							color: colors.blue[500],
							textDecoration: 'none',
							fontWeight: 'normal',
							'&:hover': {
								textDecoration: 'underline',
							},
						},
						blockquote: {
							fontWeight: '400',
							justifyItems: 'center',
						},
						h4: {
							fontSize: '1.3em',
						},
						color: colors.white,
					},
				},
			},
		},
	},
	plugins: [
		({ addUtilities, theme }) => {
			const colors = flattenColorPalette(theme('borderColor'))
			delete colors['default']

			const colorMap = Object.keys(colors).map((color) => ({
				[`.border-t-${color}`]: {
					borderTopColor: colors[color],
				},
				[`.border-r-${color}`]: {
					borderRightColor: colors[color],
				},
				[`.border-b-${color}`]: {
					borderBottomColor: colors[color],
				},
				[`.border-l-${color}`]: {
					borderLeftColor: colors[color],
				},
			}))
			const utilities = Object.assign({}, ...colorMap)

			addUtilities(utilities)
		},
		require('@tailwindcss/typography'),
		require('@tailwindcss/line-clamp'),
	],
}
