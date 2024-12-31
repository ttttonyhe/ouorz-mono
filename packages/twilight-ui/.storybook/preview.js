// Tailwind and custom CSS
import '../styles/vendor.css'
import '../styles/base.css'

import { themes } from '@storybook/theming'
import { withThemeByClassName } from '@storybook/addon-styling'

const theme = {
	brandTitle: '@twilight-toolkit/ui',
	brandUrl: 'https://ui.twilight-toolkit.ouorz.com',
}

/** @type { import('@storybook/react-webpack5').Preview } */
const preview = {
	parameters: {
		actions: { argTypesRegex: '^on[A-Z].*' },
		controls: {
			// Show full documentation for the controls
			expanded: true,
			matchers: {
				color: /(background|color)$/i,
				date: /Date$/,
			},
		},
		darkMode: {
			dark: {
				...themes.dark,
				...theme,
			},
			light: {
				...themes.normal,
				...theme,
			},
		},
	},
	decorators: [
		withThemeByClassName({
			themes: {
				light: '',
				dark: 'dark',
			},
			defaultTheme: 'light',
		}),
	],
}

export default preview
