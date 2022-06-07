// Tailwind and custom CSS
import '../styles/vendor.css'
import '../styles/base.css'

import { themes } from '@storybook/theming'

const theme = {
	brandTitle: '@twilight-toolkit/ui',
	brandUrl: 'https://ui.twilight-toolkit.ouorz.com',
}

export const parameters = {
	actions: { argTypesRegex: '^on[A-Z].*' },
	controls: {
		// Show full documentation for the controls
		expanded: true,
		matchers: {
			color: /(background|color)$/i,
			date: /Date$/,
		},
	},
	// Add a dark mode switch to toolbar
	darkMode: {
		classTarget: 'html',
		stylePreview: true,
		darkClass: 'dark',
		lightClass: 'light',
		dark: {
			...themes.dark,
			...theme,
		},
		light: {
			...themes.light,
			...theme,
		},
	},
}
